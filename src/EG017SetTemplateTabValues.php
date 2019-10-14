<?php
/**
 * Example 017: Set template field (tab) values and an envelope custom field value
 */

namespace Example;
class EG017SetTemplateTabValues
{

    private $eg = "eg017";  # reference (and url) for this example
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
        $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
        $token_ok = ds_token_ok($minimum_buffer_min);
        if ($token_ok && $template_id) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed

            $signer_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name']);
            $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
            $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
            $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
            $envelope_args = [
                'signer_email' => $signer_email,
                'signer_name' => $signer_name,
                'signer_client_id' => $this->signer_client_id,
                'cc_email' => $cc_email,
                'cc_name' => $cc_name,
                'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return',
                'template_id' => $template_id
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

                # Redirect the user to the Signing Ceremony
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl (see the makeRecipientViewRequest method)
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
            $GLOBALS['twig']->display('eg017_set_template_tab_values.html', [
                'title' => "Set template field values",
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
     * Creates envelope definition using a template.
     * The signer role will include values for the fields
     * @param $args parameters for the envelope:
     *              signer_email, signer_name, signer_client_id
     * @return mixed -- returns an envelope definition
     */
    private function make_envelope($args)
    {
        # create the envelope definition with the template_id
        $envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition([
            'status' => 'sent', 'template_id' => $args['template_id']
        ]);

        # Set the values for the fields in the template
        $check1 = new \DocuSign\eSign\Model\Checkbox([
            'tab_label' => 'ckAuthorization', 'selected' => "true"]);
        $check3 = new \DocuSign\eSign\Model\Checkbox([
            'tab_label' => 'ckAgreement', 'selected' => "true"]);
        $number1 = new \DocuSign\eSign\Model\Number([
            'tab_label' => "numbersOnly", 'value' => '54321']);
        $radio_group = new \DocuSign\eSign\Model\RadioGroup(['group_name' => "radio1",
            # You only need to provide the radio entry for the entry you're selecting
            'radios' => [
                new \DocuSign\eSign\Model\Radio(['value' => "white", 'selected' => "true"]),
            ]]);
        $text = new \DocuSign\eSign\Model\Text([
            'tab_label' => "text", 'value' => "Jabberwocky!"]);

        # We can also add a new field to the ones already in the template:
        $text_extra = new \DocuSign\eSign\Model\Text([
            'document_id' => "1", 'page_number' => "1",
            'x_position' => "280", 'y_position' => "172",
            'font' => "helvetica", 'font_size' => "size14", 'tab_label' => "added text field",
            'height' => "23", 'width' => "84", 'required' => "false",
            'bold' => 'true', 'value' => $args['signer_name'],
            'locked' => 'false', 'tab_id' => 'name']);

        # Pull together the existing and new tabs in a Tabs object:
        $tabs = new \DocuSign\eSign\Model\Tabs([
            'checkbox_tabs' => [$check1, $check3], 'number_tabs' => [$number1],
            'radio_group_tabs' => [$radio_group], 'text_tabs' => [$text, $text_extra]]);

        # Create the template role elements to connect the signer and cc recipients
        # to the template
        $signer = new \DocuSign\eSign\Model\TemplateRole([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'role_name' => 'signer',
            'client_user_id' => $args['signer_client_id'], # change the signer to be embedded
            'tabs' => $tabs # Set tab values
        ]);
        # Create a cc template role.
        $cc = new \DocuSign\eSign\Model\TemplateRole([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'role_name' => 'cc'
        ]);

        # Add the TemplateRole objects to the envelope object
        $envelope_definition->setTemplateRoles([$signer, $cc]);

        # Create an envelope custom field to save the our application's
        # data about the envelope
        $custom_field = new \DocuSign\eSign\Model\TextCustomField([
            'name' => 'app metadata item',
            'required' => 'false',
            'show' => 'true', # Yes, include in the CoC
            'value' => '1234567']);
        $custom_fields = new \DocuSign\eSign\Model\CustomFields([
            'text_custom_fields' => [$custom_field]]);
        $envelope_definition->setCustomFields($custom_fields);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end

    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg017_set_template_tab_values.html', [
                'title' => "Set template tab values",
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
