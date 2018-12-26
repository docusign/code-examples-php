<?php
/**
 * Example 013: Embedded Signing Ceremony from template with added document
 */

namespace Example;
class EG013AddDocToTemplate
{
    private $eg = "eg013";  # reference (and url) for this example

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
        $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
        $token_ok = ds_token_ok($minimum_buffer_min);
        if ($token_ok && $template_id) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name' ]);
            $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
            $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
            $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
            $item         = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['item'        ]);
            $quantity     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['quantity'    ]);
            $quantity = intval($quantity);
            $envelope_args = [
                'signer_email' => $signer_email,
                'signer_name' => $signer_name,
                'signer_client_id' => 1000,
                'cc_email' => $cc_email,
                'cc_name' => $cc_name,
                'item' => $item,
                'quantity' => $quantity,
                'template_id' => $template_id,
                'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
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
                # Redirect the user to the signing ceremony
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl
                header('Location: ' . $results["redirect_url"]);
                exit;
            }
        } elseif (! $token_ok) {
            flash('Sorry, you need to re-authenticate.');
            # We could store the parameters of the requested operation
            # so it could be restarted automatically.
            # But since it should be rare to have a token issue here,
            # we'll make the user re-enter the form data after
            # authentication.
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        } elseif (! $template_id) {
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg013_add_doc_to_template.html', [
                'title' => "Embedded Signing Ceremony from template and extra doc",
                'template_ok' => false,
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
            ]);
            exit;
        }
    }


    /**
     * Do the work of the example
     * Create the envelope and the embedded Signing Ceremony
     * 1. Create the envelope request object using composite template to
     *    add the new document
     * 2. Send the envelope
     * 3. Make the recipient view request object
     * 4. Get the recipient view (Signing Ceremony) url
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

        # 3. Create the Recipient View request object
        $authentication_method = 'None'; # How is this application authenticating
        # the signer? See the `authenticationMethod' definition
        # https://developers.docusign.com/esign-rest-api/reference/Envelopes/EnvelopeViews/createRecipient
        $recipient_view_request = new \DocuSign\eSign\Model\RecipientViewRequest([
            'authentication_method' => $authentication_method,
            'client_user_id' => $envelope_args['signer_client_id'],
            'recipient_id' => '1',
            'return_url' => $envelope_args['ds_return_url'],
            'user_name' => $envelope_args['signer_name'], 'email' => $envelope_args['signer_email']
        ]);
        # 4. Obtain the recipient_view_url for the signing ceremony
        # Exceptions will be caught by the calling function
        $results = $envelope_api->createRecipientView($args['account_id'], $envelope_id,
            $recipient_view_request);

        return ['envelope_id' => $envelope_id, 'redirect_url' => $results['url']];
    }

    /**
     * Creates the envelope definition
     * Uses compositing templates to add a new document to the existing template
     * The envelope request object uses Composite Template to
     * include in the envelope:
     * 1. A template stored on the DocuSign service
     * 2. An additional document which is a custom HTML source document
     *
     * @param $args parameters for the envelope:
     *              signer_email, signer_name, signer_client_id
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope($args)
    {
        # 1. Create Recipients for server template. Note that Recipients object
        #    is used, not TemplateRole
        #
        # Create a signer recipient for the signer role of the server template
        $signer1 = new \DocuSign\eSign\Model\Signer([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'role_name' => "signer", 'recipient_id' => "1",
            # Adding clientUserId transforms the template recipient
            # into an embedded recipient:
            'client_user_id' => $args['signer_client_id']
        ]);
        # Create the cc recipient
        $cc1 = new \DocuSign\eSign\Model\CarbonCopy([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'role_name' => "cc", 'recipient_id' =>"2"
        ]);
        # Recipients object:
        $recipients_server_template = new \DocuSign\eSign\Model\Recipients([
            'carbon_copies' => [$cc1], 'signers' => [$signer1]]);

        # 2. create a composite template for the Server template + roles
        $comp_template1 = new \DocuSign\eSign\Model\CompositeTemplate([
            'composite_template_id' => "1",
            'server_templates' => [
                new \DocuSign\eSign\Model\ServerTemplate([
                    'sequence' => "1", 'template_id' => $args['template_id']])
            ],
            # Add the roles via an inlineTemplate
            'inline_templates' => [
                new \DocuSign\eSign\Model\InlineTemplate([
                    'sequence' => "1",
                    'recipients' => $recipients_server_template])
            ]
        ]);

        # Next, create the second composite template that will
        # include the new document.
        #
        # 3. Create the signer recipient for the added document
        #    starting with the tab definition:
        $sign_here1 = new \DocuSign\eSign\Model\SignHere([
            'anchor_string' => '**signature_1**',
            'anchor_y_offset' => '10', 'anchor_units' => 'pixels',
            'anchor_x_offset' =>'20']);
        $signer1_tabs = new \DocuSign\eSign\Model\Tabs([
            'sign_here_tabs' => [$sign_here1]]);

        # 4. Create Signer definition for the added document
        $signer1AddedDoc = new \DocuSign\eSign\Model\Signer([
            'email' => $args['signer_email'],
            'name' => $args['signer_name'],
            'role_name' => "signer", 'recipient_id' => "1",
            'client_user_id' => $args['signer_client_id'],
            'tabs' => $signer1_tabs]);

        # 5. The Recipients object for the added document.
        #    Using cc1 definition from above.
        $recipients_added_doc = new \DocuSign\eSign\Model\Recipients([
            'carbon_copies' => [$cc1], 'signers' => [$signer1AddedDoc]]);

        # 6. Create the HTML document that will be added to the envelope
        $doc1_b64 = base64_encode($this->create_document1($args));
        $doc1 = new \DocuSign\eSign\Model\Document([
            'document_base64' => $doc1_b64,
            'name' => 'Appendix 1--Sales order', # can be different from
                                                 # actual file name
            'file_extension' => 'html', 'document_id' =>'1']);

        # 6. create a composite template for the added document
        $comp_template2 = new \DocuSign\eSign\Model\CompositeTemplate([
            'composite_template_id' => "2",
            # Add the recipients via an inlineTemplate
            'inline_templates' => [
                new \DocuSign\eSign\Model\InlineTemplate([
                    'sequence' => "2", 'recipients' => $recipients_added_doc])
            ],
            'document' => $doc1]);

        # 7. create the envelope definition with the composited templates
        $envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition([
            'status' => "sent",
            'composite_templates' => [$comp_template1, $comp_template2]
        ]);

        return $envelope_definition;
    }

    /**
     * Creates a customized html document for the envelope
     * @param args $
     * @return string -- the html document
     */
    private function create_document1($args)
    {
        return <<< heredoc
    <!DOCTYPE html>
    <html>
        <head>
          <meta charset="UTF-8">
        </head>
        <body style="font-family:sans-serif;margin-left:2em;">
        <h1 style="font-family: 'Trebuchet MS', Helvetica, sans-serif;
color: darkblue;margin-bottom: 0;">World Wide Corp</h1>
        <h2 style="font-family: 'Trebuchet MS', Helvetica, sans-serif;
margin-top: 0px;margin-bottom: 3.5em;font-size: 1em;
color: darkblue;">Order Processing Division</h2>
        <h4>Ordered by {$args['signer_name']}</h4>
        <p style="margin-top:0em; margin-bottom:0em;">Email: {$args['signer_email']}</p>
        <p style="margin-top:0em; margin-bottom:0em;">Copy to: {$args['cc_name']}, {$args['cc_email']}</p>
        <p style="margin-top:3em; margin-bottom:0em;">Item: <b>{$args['item']}</b>, quantity: <b>{$args['quantity']}</b> at market price.</p>
        <p style="margin-top:3em;">
  Candy bonbon pastry jujubes lollipop wafer biscuit biscuit. Topping brownie sesame snaps sweet roll pie. Croissant danish biscuit soufflé caramels jujubes jelly. Dragée danish caramels lemon drops dragée. Gummi bears cupcake biscuit tiramisu sugar plum pastry. Dragée gummies applicake pudding liquorice. Donut jujubes oat cake jelly-o. Dessert bear claw chocolate cake gummies lollipop sugar plum ice cream gummies cheesecake.
        </p>
        <!-- Note the anchor tag for the signature field is in white. -->
        <h3 style="margin-top:3em;">Agreed: <span style="color:white;">**signature_1**/</span></h3>
        </body>
    </html>
heredoc;
    }
    # ***DS.snippet.0.end


    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $basename = basename(__FILE__);
            $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
            $GLOBALS['twig']->display('eg013_add_doc_to_template.html', [
                'title' => "Embedded Signing Ceremony from template and extra doc",
                'template_ok' => $template_id,
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                'signer_email' => $GLOBALS['DS_CONFIG']['signer_email']
            ]);
        } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }
}


