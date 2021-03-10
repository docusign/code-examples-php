<?php
/**
 * Example 014: Remote signer, cc; envelope has an order form
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\FormulaTab;
use DocuSign\eSign\Model\ModelList;
use DocuSign\eSign\Model\PaymentDetails;
use DocuSign\eSign\Model\PaymentLineItem;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG014CollectPayment extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg014";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Envelope sent",
                    "Envelope sent",
                    "The envelope has been created and sent!<br/>
                        Envelope ID {$results["envelope_id"]}."
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): array
    {
        # 1. Create the envelope request object
        $envelope_definition = $this->make_envelope($args["envelope_args"]);

        # 2. call Envelopes::create API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();
        $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);

        return ['envelope_id' => $results->getEnvelopeId()];
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
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope(array $args): EnvelopeDefinition
    {
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
        $doc1_file = 'order_form.html';
        $doc1_html_v1 = file_get_contents(self::DEMO_DOCS_PATH . $doc1_file);

        # Substitute values into the HTML
        # Substitute for: {signerName}, {signerEmail}, {cc_name}, {cc_email}
        $doc1_html_v2 = str_replace(
            ['{signer_email}'     , '{cc_name}'     , '{cc_email}'     ],
            [$args['signer_email'], $args['cc_name'], $args['cc_email']],
            $doc1_html_v1
        );

        # create the envelope definition
        $envelope_definition = new EnvelopeDefinition([
            'email_subject' => 'Please complete your order',
            'status' => 'sent']);

        # add the document
        $doc1_b64 = base64_encode($doc1_html_v2);
        $doc1 = new Document([
            'document_base64' => $doc1_b64,
            'name' => 'Order form', # can be different from actual file name
            'file_extension' => 'html', # Source data format.
            'document_id' => '1' # a label used to reference the doc
        ]);
        $envelope_definition->setDocuments([$doc1]);

        # create a signer recipient to sign the document
        $signer1 = new Signer([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipient_id' => "1", 'routing_order'=> "1"]);
        # create a cc recipient to receive a copy of the documents
        $cc1 = new CarbonCopy([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'recipient_id' => "2", 'routing_order' => "2"]);

        # Create signHere fields (also known as tabs) on the documents,
        # We're using anchor (autoPlace) positioning
        $sign_here1 = new SignHere([
            'anchor_string' => '/sn1/',
            'anchor_y_offset' => '10', 'anchor_units' => 'pixels',
            'anchor_x_offset' =>'20']);

        $list1 = new ModelList([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l1q/',
            'anchor_y_offset' => '-10', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'list_items' => [
                ['text' => "Red"   , 'value' => "red"   ], ['text' => "Orange", 'value' => "orange"],
                ['text' => "Yellow", 'value' => "yellow"], ['text' => "Green" , 'value' => "green" ],
                ['text' => "Blue"  , 'value' => "blue"  ], ['text' => "Indigo", 'value' => "indigo"]
            ],
            'required' => "true",
            'tab_label' => "l1q"
        ]);

        $list2 = new ModelList([
            'font' => "helvetica",
            'font_size' => "size11",
            'anchor_string' => '/l1q/',
            'anchor_y_offset' => '-10', 'anchor_units' => 'pixels',
            'anchor_x_offset' => '0',
            'list_items' => [
                ['text' => "Red"   , 'value' => "red"   ], ['text' => "Orange", 'value' => "orange"],
                ['text' => "Yellow", 'value' => "yellow"], ['text' => "Green" , 'value' => "green" ],
                ['text' => "Blue"  , 'value' => "blue"  ], ['text' => "Indigo", 'value' => "indigo"]
            ],
            'required' => "true",
            'tab_label' => "l2q"
        ]);

        # create two formula tabs for the extended price on the line items
        $formulal1e = new FormulaTab([
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
        $formulal2e = new FormulaTab([
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
        $formulal3t = new FormulaTab([
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
        $payment_line_iteml1 = new PaymentLineItem([
            'name' => $l1_name, 'description' => $l1_description,
            'amount_reference' => "l1e"]);
        $payment_line_iteml2 = new PaymentLineItem([
            'name' => $l2_name, 'description' => $l2_description,
            'amount_reference' => "l2e"]);
        $payment_details = new PaymentDetails([
            'gateway_account_id' => $args['gateway_account_id'],
            'currency_code' => "USD",
            'gateway_name' => $args['gateway_name'],
            'line_items' => [$payment_line_iteml1, $payment_line_iteml2]]);
        # Hidden formula for the payment itself
        $formula_payment = new FormulaTab([
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
        $signer1_tabs = new Tabs([
            'sign_here_tabs' => [$sign_here1],
            'list_tabs' => [$list1, $list2],
            'formula_tabs' => [$formulal1e, $formulal2e,
                $formulal3t, $formula_payment]]);
        $signer1->setTabs($signer1_tabs);

        # Add the recipients to the envelope object
        $recipients = new Recipients([
            'signers' => [$signer1], 'carbon_copies' => [$cc1]]);
        $envelope_definition->setRecipients($recipients);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
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

        return $args;
    }
}
