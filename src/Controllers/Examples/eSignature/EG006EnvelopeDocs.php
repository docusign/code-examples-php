<?php

/**
 * Example 006: List an envelope's documents
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\EnvelopeDocsService;

class EG006EnvelopeDocs extends eSignBaseController
{
    const EG = 'eg006'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();
        $envelope_id = $this->args['envelope_id'];
        if ($envelope_id) {
            # 2. Call the worker method
            $envelopeDocumentsResult = EnvelopeDocsService::envelopeDocs($this->args, $this->clientService);

            if ($envelopeDocumentsResult) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $envelopeDocumentsResult = json_decode((string)$envelopeDocumentsResult, true);

                # Save the envelope_id and its list of documents in the session so
                # they can be used in example 7 (download a document)
                $standard_doc_items = [
                    ['name' => 'Combined', 'type' => 'content', 'document_id' => 'combined'],
                    ['name' => 'Zip archive', 'type' => 'zip', 'document_id' => 'archive']
                ];
                # The certificate of completion is named "summary".
                # We give it a better name below.
                $map_documents = function ($doc) {
                    if ($doc['documentId'] == "certificate") {
                        $new = [
                            'document_id' => $doc['documentId'],
                            'name' => "Certificate of completion",
                            'type' => $doc['type']
                        ];
                    } else {
                        $new = ['document_id' => $doc['documentId'], 'name' => $doc['name'], 'type' => $doc['type']];
                    }
                    return $new;
                };
                $envelope_doc_items = array_map($map_documents, $envelopeDocumentsResult['envelopeDocuments']);
                $documents = array_merge($standard_doc_items, $envelope_doc_items);
                $_SESSION['envelope_documents'] = ['envelope_id' => $envelope_id, 'documents' => $documents]; # Save
                $this->clientService->showDoneTemplate(
                    "Envelope documents list",
                    "List the envelope's documents",
                    "Results from the EnvelopeDocuments::list method:",
                    json_encode(json_encode($envelopeDocumentsResult))
                );
            }
        } else {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this::EG),
                $this->routerService->getTitle($this::EG),
                $this::EG,
                ['envelope_ok' => false]
            );
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $envelope_id = $_SESSION['envelope_id'] ?? false;
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id
        ];
    }
}
