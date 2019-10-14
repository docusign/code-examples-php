<?php
/**
 * Example 006: List an envelope's documents
 */

namespace Example;
class EG006EnvelopeDocs
{

    private $eg = "eg006";  # reference (and url) for this example

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

                # Save the envelopeId and its list of documents in the session so
                # they can be used in example 7 (download a document)
                $standard_doc_items = [
                    ['name' => 'Combined'   , 'type' => 'content', 'document_id' => 'combined'],
                    ['name' => 'Zip archive', 'type' => 'zip'    , 'document_id' => 'archive']];
                # The certificate of completion is named "summary".
                # We give it a better name below.
                $map_documents = function ($doc) {
                    if ($doc['documentId'] == "certificate") {
                        $new = ['document_id' => $doc['documentId'], 'name' => "Certificate of completion",
                                'type' => $doc['type']];
                    } else {
                        $new = ['document_id' => $doc['documentId'], 'name' => $doc['name'], 'type' => $doc['type']];
                    }
                    return $new;
                };
                $envelope_doc_items = array_map($map_documents, $results['envelopeDocuments']);
                $documents = array_merge($standard_doc_items, $envelope_doc_items);
                $envelope_documents = ['envelope_id' => $envelope_id, 'documents' => $documents];
                $_SESSION['envelope_documents'] = $envelope_documents; # Save

                $GLOBALS['twig']->display('example_done.html', [
                    'title' => "Envelope documents list",
                    'h1' => "List the envelope's documents",
                    'message' => "Results from the EnvelopeDocuments::list method:",
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
            $GLOBALS['twig']->display('eg006_envelope_docs.html', [
                'title' => "Envelope documents",
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
     * 1. Call the envelope documents list method
     * @param $args
     * @return \DocuSign\eSign\Model\EnvelopeDocumentsResult
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

        $results = $envelope_api->listDocuments($args['account_id'], $args['envelope_id']);
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
            $GLOBALS['twig']->display('eg006_envelope_docs.html', [
                'title' => "Envelope documents",
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

