<?php
/**
 * Example 007: Get an envelope's document
 */

namespace Example;
class EG007EnvelopeGetDoc
{

    private $eg = "eg007";  # reference (and url) for this example

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
        $envelope_documents = isset($_SESSION['envelope_documents']) ? $_SESSION['envelope_documents'] : false;
        $token_ok = ds_token_ok($minimum_buffer_min);
        if ($token_ok && $envelope_id && $envelope_documents) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $document_id  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['document_id' ]);
            $args = [
                'account_id' => $_SESSION['ds_account_id'],
                'base_path' => $_SESSION['ds_base_path'],
                'ds_access_token' => $_SESSION['ds_access_token'],
                'envelope_id' => $envelope_id,
                'document_id' => $document_id,
                'envelope_documents' => $envelope_documents
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
                # See https://stackoverflow.com/a/27805443/64904
                header("Content-Type: {$results['mimetype']}");
                header("Content-Disposition: attachment; filename=\"{$results['doc_name']}\"");
                ob_clean();
                flush();
                $file_path = $results['data']->getPathname();
                readfile($file_path);
                exit();
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
        } elseif (! $envelope_id || !$envelope_documents) {
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg007_envelope_get_doc.html', [
                'title' => "Download an envelope's document",
                'envelope_ok' => false,
                'documents_ok' => false,
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
     * @return array
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
        $document_id = $args['document_id'];

        # An SplFileObject is returned. See http://php.net/manual/en/class.splfileobject.php
        $temp_file = $envelope_api->getDocument($args['account_id'], $document_id, $args['envelope_id']);
        # find the matching document information item
        $doc_item = false;
        foreach ($args['envelope_documents']['documents'] as $item) {
            if ($item['document_id'] == $document_id) {
                $doc_item = $item;
                break;
            }
        }
        $doc_name = $doc_item['name'];
        $has_pdf_suffix = strtoupper(substr($doc_name, -4)) == '.PDF';
        $pdf_file = $has_pdf_suffix;
        # Add ".pdf" if it's a content or summary doc and doesn't already end in .pdf
        if ($doc_item["type"] == "content" || ($doc_item["type"] == "summary" && ! $has_pdf_suffix)) {
            $doc_name .= ".pdf";
            $pdf_file = true;
        }
        # Add .zip as appropriate
        if ($doc_item["type"] == "zip") {
            $doc_name .= ".zip";
        }
        # Return the file information
        if ($pdf_file) {
            $mimetype = 'application/pdf';
        } elseif ($doc_item["type"] == 'zip') {
            $mimetype = 'application/zip';
        } else {
            $mimetype = 'application/octet-stream';
        }

    return ['mimetype' => $mimetype, 'doc_name' => $doc_name, 'data' => $temp_file];
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
            $envelope_documents = isset($_SESSION['envelope_documents']) ? $_SESSION['envelope_documents'] : false;

            $document_options = [];
            if ($envelope_documents) {
                # Prepare the select items
                $cb = function ($item) {
                    return ['text' => $item['name'], 'document_id' => $item['document_id']];
                };
                $document_options = array_map($cb, $envelope_documents['documents']);
            }

            $GLOBALS['twig']->display('eg007_envelope_get_doc.html', [
                'title' => "Envelope documents",
                'envelope_ok' => $envelope_id,
                'documents_ok' => $envelope_documents,
                'document_options' => $document_options,
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

