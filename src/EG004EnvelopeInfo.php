<?php
/**
 * Example 004: Get an envelope's basic information and status
 */

namespace Example;
class EG004EnvelopeInfo
{

    private $eg = "eg004";  # reference (and url) for this example

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
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     */
    private function createController()
    {
        $minimum_buffer_min = 3;
        $envelope_id = isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
        $token_ok = ds_token_ok($minimum_buffer_min);
        if ($token_ok && $envelope_id) {
            # 2. Call the worker method
            $args = [
                'account_id' => $_SESSION['ds_account_id'],
                'base_path' => $_SESSION['ds_base_path'],
                'ds_access_token' => $_SESSION['ds_access_token'],
                'envelope_id' => $envelope_id
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
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);
                $GLOBALS['twig']->display('example_done.html', [
                    'title' => "Envelope status results",
                    'h1' => "Envelope status results",
                    'message' => "Results from the Envelopes::get method:",
                    'json' => json_encode(json_encode($results))
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
        } elseif (! $envelope_id) {
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg004_envelope_info.html', [
                'title' => "Envelope information",
                'envelope_ok' => false,
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
            ]);
        }
    }


    /**
     * Do the work of the example
     * 1. Get the envelope's data
     * @param $args
     * @return \DocuSign\eSign\Model\Envelope
     * @throws \DocuSign\eSign\ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args)
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $config = new \DocuSign\eSign\Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $api_client = new \DocuSign\eSign\ApiClient($config);
        $envelope_api = new \DocuSign\eSign\Api\EnvelopesApi($api_client);

        $results = $envelope_api->getEnvelope($args['account_id'], $args['envelope_id']);
        return $results;
    }
    # ***DS.snippet.0.end

    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $basename = basename(__FILE__);
            $envelope_id = isset($_SESSION['envelope_id']) && $_SESSION['envelope_id'];
            $GLOBALS['twig']->display('eg004_envelope_info.html', [
                'title' => "Envelope information",
                'envelope_ok' => $envelope_id,
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
            ]);
        } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }
}

