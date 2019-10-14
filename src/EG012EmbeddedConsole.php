<?php
/**
 * Example 012: Embedded console
 */

namespace Example;
class EG012EmbeddedConsole
{
    private $eg = "eg012";  # reference (and url) for this example

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
        $envelope_id = isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
        $token_ok = ds_token_ok($minimum_buffer_min);
        if ($token_ok) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $starting_view = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['starting_view']);
            $args = [
                'envelope_id' => $envelope_id,
                'account_id' => $_SESSION['ds_account_id'],
                'base_path' => $_SESSION['ds_base_path'],
                'ds_access_token' => $_SESSION['ds_access_token'],
                'starting_view' => $starting_view,
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
     * Set the url where you want the recipient to go once they are done
     * with the NDSE. It is usually the case that the
     * user will never "finish" with the NDSE.
     * Assume that control will not be passed back to your app.
     * @param $args
     * @return array ['redirect_url']
     * @throws \DocuSign\eSign\ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args)
    {
        # Step 1. Create the NDSE view request object
        # Exceptions will be caught by the calling function
        $view_request = new \DocuSign\eSign\Model\ConsoleViewRequest(['return_url' => $args['ds_return_url']]);
        if ($args['starting_view'] == "envelope" && $args['envelope_id']) {
            $view_request->setEnvelopeId($args['envelope_id']);
        }

        # 2. Call the API method
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $api_client = new \DocuSign\eSign\ApiClient($config);
        $envelope_api = new \DocuSign\eSign\Api\EnvelopesApi($api_client);
        $results = $envelope_api->createConsoleView($args['account_id'], $view_request);
        $url = $results['url'];
        return ['redirect_url' =>  $url];
    }
    # ***DS.snippet.0.end

    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $basename = basename(__FILE__);
            $envelope_id = isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
            $GLOBALS['twig']->display('eg012_embedded_console.html', [
                'title' => "Embedded Console",
                'envelope_ok' => $envelope_id,
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

