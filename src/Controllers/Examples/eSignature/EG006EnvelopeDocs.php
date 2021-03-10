<?php
/**
 * Example 006: List an envelope's documents
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\EnvelopeDocumentsResult;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG006EnvelopeDocs extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg006";  # reference (and url) for this example

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
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $envelope_id= $this->args['envelope_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $envelope_id) {
            # 2. Call the worker method
            $results = $this->worker($this->args);

            if ($results) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);

                # Save the envelope_id and its list of documents in the session so
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
                $_SESSION['envelope_documents'] = ['envelope_id' => $envelope_id, 'documents' => $documents]; # Save
                $this->clientService->showDoneTemplate(
                    "Envelope documents list",
                    "List the envelope's documents",
                    "Results from the EnvelopeDocuments::list method:",
                    json_encode(json_encode($results))
                );
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $envelope_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this->eg),
                $this->routerService->getTitle($this->eg),
                $this->eg,
                ['envelope_ok' => false]
            );
        }
    }


    /**
     * Do the work of the example
     * 1. Call the envelope documents list method
     *
     * @param  $args array
     * @return EnvelopeDocumentsResult
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): EnvelopeDocumentsResult
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();
        try {
            $results = $envelope_api->listDocuments($args['account_id'], $args['envelope_id']);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $envelope_id= isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id
        ];

        return $args;
    }
}

