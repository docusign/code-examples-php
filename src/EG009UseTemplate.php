<?php
/**
 * Example 009: Send envelope using a template
 */

namespace Example;
class EG009UseTemplate
{
    private $eg = "eg009";  # reference (and url) for this example

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
            $envelope_args = [
                'signer_email' => $signer_email,
                'signer_name' => $signer_name,
                'cc_email' => $cc_email,
                'cc_name' => $cc_name,
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
                $GLOBALS['twig']->display('example_done.html', [
                    'title' => "Envelope sent",
                    'h1' => "Envelope sent",
                    'message' => "The envelope has been created and sent!<br/>
                        Envelope ID {$results["envelope_id"]}."
                ]);
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
            $GLOBALS['twig']->display('eg009_use_template.html', [
                'title' => "Use a template to send an envelope",
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
        return ['envelope_id' => $envelope_id];
    }

    /**
     * Creates envelope definition using a template
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
        # Create the template role elements to connect the signer and cc recipients
        # to the template
        $signer = new \DocuSign\eSign\Model\TemplateRole([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'role_name' => 'signer'
        ]);
        # Create a cc template role.
        $cc = new \DocuSign\eSign\Model\TemplateRole([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'role_name' => 'cc'
        ]);

        # Add the TemplateRole objects to the envelope object
        $envelope_definition->setTemplateRoles([$signer, $cc]);
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
            $GLOBALS['twig']->display('eg009_use_template.html', [
                'title' => "Use a template to send an envelope",
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

