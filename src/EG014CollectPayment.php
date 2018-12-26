<?php
/**
 * Example 014: Remote signer, cc; envelope has an order form
 */

namespace Example;
class EG014CollectPayment
{
    private $eg = "eg014";  # reference (and url) for this example

    public function controller()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController();
        };
        if ($method == 'POST') {
            check_csrf();
            $this->createController();
        };
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     */
    private function createController()
    {
        $minimum_buffer_min = 3;
        if (ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name' ]);
            $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
            $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
            $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
            $envelope_args = [
                'signer_email' => $signer_email,
                'signer_name' => $signer_name,
                'cc_email' => $cc_email,
                'cc_name' => $cc_name,
                'gateway_account_id' => $GLOBALS['DS_CONFIG']['gateway_account_id'],
                'gateway_name' => $GLOBALS['DS_CONFIG']['gateway_name'],
                'gateway_display_name' => $GLOBALS['DS_CONFIG']['gateway_display_name']
            ];
            $args = [
                'account_id' => $_SESSION['ds_account_id'],
                'base_path' => $_SESSION['ds_base_path'],
                'ds_access_token' => $_SESSION['ds_access_token'],
                'envelope_args' => $envelope_args
            ];

            try {
                $results = $this->worker($args);

            } catch (\DocuSign\eSign\ApiException $e) {
                $error_code = $e->getResponseBody()->errorCode;
                $error_message = $e->getResponseBody()->message;
                $GLOBALS['twig']->display('error.html', [
                        'error_code' => $error_code,
                        'error_message' => $error_message]
                );
                exit();
            }
            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelopeId
                $GLOBALS['twig']->display('example_done.html', [
                    'title' => "Envelope sent",
                    'h1' => "Envelope sent",
                    'message' => "The envelope has been created and sent!<br/>
                        Envelope ID {$results["envelope_id"]}."
                ]);
                exit;
            }
        } else {
            flash('Sorry, you need to re-authenticate.');
            # We could store the parameters of the requested operation
            # so it could be restarted automatically.
            # But since it should be rare to have a token issue here,
            # we'll make the user re-enter the form data after
            # authentication.
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }


    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * @param $args
     * @return array ['redirect_url']
     * @throws \DocuSign\eSign\ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    public function worker($args)
    {
        $envelope_args = $args["envelope_args"];
        # 1. Create the envelope request object
        $envelope_definition = $this->make_envelope($envelope_args);

        # 2. call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $api_client = new \DocuSign\eSign\ApiClient($config);
        $envelope_api = new \DocuSign\eSign\Api\EnvelopesApi($api_client);
        $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        $envelope_id = $results->getEnvelopeId();
        return ['envelope_id' => $envelope_id];
    }

    /**
     * This function creates the envelope definition for the
     * order form.
     * document 1 (html) has multiple tags:
     * /l1q/ and /l2q/ -- quantities: drop down
     * /l1e/ and /l2e/ -- extended: payment lines
     * /l3t/ -- total -- formula
     *
     * The envelope has two recipients.
     * recipient 1 - signer
     * recipient 2 - cc
     * The envelope will be sent first to the signer.
     * After it is signed, a copy is sent to the cc person.
     *
     *
     * #################################################################
     * #                                                               #
     * # NOTA BENA: This method programmatically constructs the        #
     * #            order form. For many use cases, it would be        #
     * #            better to create the order form as a template      #
     * #            using the DocuSign web tool as WYSIWYG             #
     * #            form designer.                                     #
     * #                                                               #
     * #################################################################
     *
     *
     * @param $args parameters for the envelope:
     *              signer_email, signer_name, signer_client_id
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope($args)
    {


        # Unfortunately there is currently a bug in the PHP SDK such that creating
        # a list tab is not yet supported via the SDK. You can use the API directly if need be, see eg010 file.
        # See https://github.com/docusign/docusign-php-client/issues/58
        # Once the bug is fixed something similar to the following will be used:


        # Order form constants
        $l1_name = "Harmonica";
        $l1_price = 5;
        $l1_description = "{$l1_price} each";
        $l2_name = "Xylophone";
        $l2_price = 150;
        $l2_description = "{$l2_price} each";
        $currency_multiplier = 100;

        # read the html file from a local directory
        # The read could raise an exception if the file is not available!
        $demo_docs_path = __DIR__ . '/../public/demo_documents/';
        $doc1_file = 'order_form.html';
        $doc1_html_v1 = file_get_contents($demo_docs_path . $doc1_file);

        # Substitute values into the HTML
        # Substitute for: {signerName}, {signerEmail}, {cc_name}, {cc_email}
        $doc1_html_v2 = str_replace(
            ['{signer_email}'     , '{cc_name}'     , '{cc_email}'     ],
            [$args['signer_email'], $args['cc_name'], $args['cc_email']],
            $doc1_html_v1
        );

        # create the envelope definition
        $envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition([
            'email_subject' => 'Please complete your order',
            'status' => 'sent']);

        # add the document
        $doc1_b64 = base64_encode($doc1_html_v2);
        $doc1 = new \DocuSign\eSign\Model\Document([
            'document_base64' => $doc1_b64,
            'name' => 'Order form', # can be different from actual file name
            'file_extension' => 'html', # Source data format.
            'document_id' => '1' # a label used to reference the doc
        ]);
        $envelope_definition->setDocuments([$doc1]);

        # create a signer recipient to sign the document
        $signer1 = new \DocuSign\eSign\Model\Signer([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipient_id' => "1", 'routing_order'=> "1"]);
        # create a cc recipient to receive a copy of the documents
        $cc1 = new \DocuSign\eSign\Model\CarbonCopy([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'recipient_id' => "2", 'routing_order' => "2"]);

        # Create signHere fields (also known as tabs) on the documents,
        # We're using anchor (autoPlace) positioning
        $sign_here1 = new \DocuSign\eSign\Model\SignHere([
            'anchor_string' => '/sn1/',
            'anchor_y_offset' => '10', 'anchor_units' => 'pixels',
            'anchor_x_offset' =>'20']);
        $list_item0  = new \DocuSign\eSign\Model\ListItem(['text' => "none", 'value' => "0" ]);
        $list_item1  = new \DocuSign\eSign\Model\ListItem(['text' => "1"   , 'value' => "1" ]);
        $list_item2  = new \DocuSign\eSign\Model\ListItem(['text' => "2"   , 'value' => "2" ]);
        $list_item3  = new \DocuSign\eSign\Model\ListItem(['text' => "3"   , 'value' => "3" ]);
        $list_item4  = new \DocuSign\eSign\Model\ListItem(['text' => "4"   , 'value' => "4" ]);
        $list_item5  = new \DocuSign\eSign\Model\ListItem(['text' => "5"   , 'value' => "5" ]);
        $list_item6  = new \DocuSign\eSign\Model\ListItem(['text' => "6"   , 'value' => "6" ]);
        $list_item7  = new \DocuSign\eSign\Model\ListItem(['text' => "7"   , 'value' => "7" ]);
        $list_item8  = new \DocuSign\eSign\Model\ListItem(['text' => "8"   , 'value' => "8" ]);
        $list_item9  = new \DocuSign\eSign\Model\ListItem(['text' => "9"   , 'value' => "9" ]);
        $list_item10 = new \DocuSign\eSign\Model\ListItem(['text' => "10"  , 'value' => "10"]);

        $listl1q = new \DocuSign\eSign\Model\ListModel([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l1q/',
            'anchor_y_offset' => '-10', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'list_items' => [$list_item0, $list_item1, $list_item2,
                $list_item3, $list_item4, $list_item5, $list_item6,
                $list_item7, $list_item8, $list_item9, $list_item10],
            'required' => "true",
            'tab_label' => "l1q"
            ]);
        $listl2q = new \DocuSign\eSign\Model\ListModel([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l2q/',
            'anchor_y_offset' => '-10', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'list_items' => [$list_item0, $list_item1, $list_item2,
                $list_item3, $list_item4, $list_item5, $list_item6,
                $list_item7, $list_item8, $list_item9, $list_item10],
            'required' => "true",
            'tab_label' => "l2q"
            ]);
        # create two formula tabs for the extended price on the line items
        $formulal1e = new \DocuSign\eSign\Model\FormulaTab([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l1e/',
            'anchor_y_offset' => '-8', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '105',
            'tab_label' => "l1e",
            'formula' => "[l1q] * {$l1_price}",
            'round_decimal_places' => "0",
            'required' => "true",
            'locked' => "true",
            'disable_auto_size' => "false",
            ]);
        $formulal2e = new \DocuSign\eSign\Model\FormulaTab([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l2e/',
            'anchor_y_offset' => '-8', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '105',
            'tab_label' => "l2e",
            'formula' => "[l2q] * {$l2_price}",
            'round_decimal_places' => "0",
            'required' => "true",
            'locked' => "true",
            'disable_auto_size' => "false",
            ]);
        # Formula for the total
        $formulal3t = new \DocuSign\eSign\Model\FormulaTab([
            'font' => "helvetica",
            'bold' => "true",
            'font_size' => "size12",
            'anchor_string' => '/l3t/',
            'anchor_y_offset' => '-8', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '50',
            'tab_label' => "l3t",
            'formula' => '[l1e] + [l2e]',
            'round_decimal_places' => "0",
            'required' => "true",
            'locked' => "true",
            'disable_auto_size' => "false",
            ]);
        # Payment line items
        $payment_line_iteml1 = new \DocuSign\eSign\Model\PaymentLineItem([
            'name' => $l1_name, 'description' => $l1_description,
            'amount_reference' => "l1e"]);
        $payment_line_iteml2 = new \DocuSign\eSign\Model\PaymentLineItem([
            'name' => $l2_name, 'description' => $l2_description,
            'amount_reference' => "l2e"]);
        $payment_details = new \DocuSign\eSign\Model\PaymentDetails([
            'gateway_account_id' => $args['gateway_account_id'],
            'currency_code' => "USD",
            'gateway_name' => $args['gateway_name'],
            'line_items' => [$payment_line_iteml1, $payment_line_iteml2]]);
        # Hidden formula for the payment itself
        $formula_payment = new \DocuSign\eSign\Model\FormulaTab([
            'tab_label' => "payment",
            'formula' => "([l1e] + [l2e]) * {$currency_multiplier}",
            'round_decimal_places' => "0",
            'payment_details' => $payment_details,
            'hidden' => "true",
            'required' => "true",
            'locked' => "true",
            'document_id' => "1",
            'page_number' => "1",
            'x_position' => "0",
            'y_position' => "0"]);

        # Tabs are set per recipient / signer
        $signer1_tabs = new \DocuSign\eSign\Model\Tabs([
            'sign_here_tabs' => [$sign_here1],
            'list_tabs' => [$listl1q, $listl2q],
            'formula_tabs' => [$formulal1e, $formulal2e,
                $formulal3t, $formula_payment]]);
        $signer1->setTabs($signer1_tabs);

        # Add the recipients to the envelope object
        $recipients = new \DocuSign\eSign\Model\Recipients([
            'signers' => [$signer1], 'carbon_copies' => [$cc1]]);
        $envelope_definition->setRecipients($recipients);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end


    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {

            $gateway = $GLOBALS['DS_CONFIG']['gateway_account_id'];
            $gateway_ok = $gateway && strlen($gateway) > 25;
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg014_collect_payment.html', [
                'title' => "Order form with payment",
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                'signer_email' => $GLOBALS['DS_CONFIG']['signer_email'],
                'gateway_ok' => $gateway_ok

            ]);
        } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }
}
