<?php

/**
 * Example 007: Get an envelope's document
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\EnvelopeGetDocService;

class EG007EnvelopeGetDoc extends eSignBaseController
{
    const EG = 'eg007'; # reference (and URL) for this example
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
     * Check the token and check we have an envelope_id
     * Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();
        $envelope_id = $this->args['envelope_id'];
        $envelope_documents = $_SESSION['envelope_documents'] ?? false;
        if ($envelope_id && $envelope_documents) {
            # Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $docNameAndData = EnvelopeGetDocService::envelopeGetDoc($this->args, $this->clientService);

            if ($docNameAndData) {
                # See https://stackoverflow.com/a/27805443/64904
                header("Content-Type: {$docNameAndData['mimetype']}");
                header("Content-Disposition: attachment; filename=\"{$docNameAndData['doc_name']}\"");
                ob_clean();
                flush();
                $file_path = $docNameAndData['data']->getPathname();
                readfile($file_path);
                exit();
            }
        } else {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this::EG),
                $this->routerService->getTitle($this::EG),
                $this::EG,
                ['envelope_ok' => false, 'documents_ok' => false]
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
        $envelope_documents = $_SESSION['envelope_documents'] ?? false;
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id,
            'document_id' => $this->checkInputValues($_POST['document_id']),
            'envelope_documents' => $envelope_documents
        ];
    }
}
