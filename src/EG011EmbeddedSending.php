<?php
/**
 * Example 011: Embedded sending: Remote signer, cc; envelope has three documents
 */

namespace Example;
class EG011EmbeddedSending
{
    private $eg = "eg011";  # reference (and url) for this example

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
            $signer_name   = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name'  ]);
            $signer_email  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email' ]);
            $cc_name       = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'      ]);
            $cc_email      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'     ]);
            $starting_view = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['starting_view']);
            $envelope_args = [
                'signer_email' => $signer_email,
                'signer_name' => $signer_name,
                'cc_email' => $cc_email,
                'cc_name' => $cc_name,
            ];
            $args = [
                'account_id' => $_SESSION['ds_account_id'],
                'base_path' => $_SESSION['ds_base_path'],
                'ds_access_token' => $_SESSION['ds_access_token'],
                'starting_view' => $starting_view,
                'envelope_args' => $envelope_args,
                'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
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
                # Redirect the user to the NDSE view
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
     * 1. Create the envelope with "created" (draft) status
     * 2. Send the envelope
     * 3. Get the SenderView url
     * @param $args
     * @return array ['redirect_url']
     * @throws \DocuSign\eSign\ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args)
    {
        # 1. Create the envelope as a draft using eg002's worker
        # Exceptions will be caught by the calling function
        $args['envelope_args']['status'] = 'created';
        $eg = new EG002SigningViaEmail();
        $results = $eg->worker($args);
        $envelope_id = $results['envelope_id'];

        # 2. Create sender view
        $view_request = new \DocuSign\eSign\Model\ReturnUrlRequest(['return_url' => $args['ds_return_url']]);
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $api_client = new \DocuSign\eSign\ApiClient($config);
        $envelope_api = new \DocuSign\eSign\Api\EnvelopesApi($api_client);
        $results = $envelope_api->createSenderView($args['account_id'], $envelope_id, $view_request);

        # Switch to the Recipients / Documents view if requested by the user in the form
        $url = $results['url'];
        if ($args['starting_view'] == "recipient") {
            $url = str_replace('send=1', 'send=0', $url);
        }

        return ['envelope_id' => $envelope_id, 'redirect_url' =>  $url];
    }
    # ***DS.snippet.0.end

    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg011_embedded_sending.html', [
                'title' => "Embedded Sending",
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

