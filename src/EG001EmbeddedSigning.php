<?php
/**
 * Example 001: Embedded Signing Ceremony
 */

namespace Example;
class EG001EmbeddedSigning
{

    private $eg = "eg001";  # reference (and url) for this example
    private $signer_client_id = 1000; # Used to indicate that the signer will use an embedded
                # Signing Ceremony. Represents the signer's userId within your application.

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
     * 3. Redirect the user to the signing ceremony
     */
    private function createController()
    {
        $minimum_buffer_min = 3;
        if (ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed

            $signer_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name']);
            $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
            $envelope_args = [
                'signer_email' => $signer_email,
                'signer_name' => $signer_name,
                'signer_client_id' => $this->signer_client_id,
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
                # Redirect the user to the Signing Ceremony
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl (see the makeRecipientViewRequest method)
                header('Location: ' . $results["redirect_url"]);
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
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the signing ceremony
     * @param $args
     * @return array ['redirect_url']
     * @throws \DocuSign\eSign\ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args)
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
     *  Creates envelope definition
     * @param $args parameters for the envelope:
     *              signer_email, signer_name, signer_client_id
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope($args)
    {
        # document 1 (pdf) has tag /sn1/
        #
        # The envelope has one recipient.
        # recipient 1 - signer
        #
        # Read the file
        $demo_docs_path = __DIR__ . '/../public/demo_documents/';
        $content_bytes = file_get_contents($demo_docs_path . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new \DocuSign\eSign\Model\Document([ # create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Example document', # can be different from actual file name
            'file_extension' => 'pdf', # many different document types are accepted
            'document_id' => 1 # a label used to reference the doc
        ]);

        # Create the signer recipient model
        $signer = new \DocuSign\eSign\Model\Signer([ # The signer
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipient_id' => "1", 'routing_order' => "1",
            # Setting the client_user_id marks the signer as embedded
            'client_user_id' => $args['signer_client_id']
        ]);

        # Create a sign_here tab (field on the document)
        $sign_here = new \DocuSign\eSign\Model\SignHere([ # DocuSign SignHere field/tab
            'anchor_string' => '/sn1/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);

        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->settabs(new \DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$sign_here]]));

        # Next, create the top level envelope definition and populate it.
        $envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new \DocuSign\eSign\Model\Recipients(['signers' => [$signer]]),
            'status' => "sent" # requests that the envelope be created and sent.
        ]);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end

    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg001_embedded_signing.html', [
                'title' => "Embedded Signing Ceremony",
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
