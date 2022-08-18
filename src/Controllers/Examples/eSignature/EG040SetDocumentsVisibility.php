<?php

/**
 * Example 040: Set document visibility.
*/

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\SetDocumentsVisibilityService;
use DocuSign\eSign\Client\ApiException;

class EG040SetDocumentsVisibility extends eSignBaseController
{
    const EG = 'eg040'; # reference (and URL) for this example
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
     * 1. Check the token
     * 2. Call the worker method
     * 3. Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        try {
            $envelopeId = SetDocumentsVisibilityService::worker(
                $this->args['signer_1_email'],
                $this->args['signer_1_name'],
                $this->args['signer_2_email'],
                $this->args['signer_2_name'],
                $this->args['cc_email'],
                $this->args['cc_name'],
                $GLOBALS['DS_CONFIG']['doc_pdf'],
                $GLOBALS['DS_CONFIG']['doc_docx'],
                $GLOBALS['DS_CONFIG']['doc_html'],
                $this->args['account_id'],
                $this->clientService,
                self::DEMO_DOCS_PATH
            );

            if ($envelopeId) {
                $this->clientService->showDoneTemplate(
                    "Set document visibility for envelope recipients",
                    "Set document visibility for envelope recipients",
                    "The envelope has been created and sent!<br/> Envelope ID {$envelopeId}."
                );
            }
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'signer_1_email' => $this->checkEmailInputValue($_POST['signer_1_email']),
            'signer_1_name' => $this->checkInputValues($_POST['signer_1_name']),
            'signer_2_email' => $this->checkInputValues($_POST['signer_2_email']),
            'signer_2_name' => $this->checkInputValues($_POST['signer_2_name']),
            'cc_email' => $this->checkEmailInputValue($_POST['cc_email']),
            'cc_name' => $this->checkInputValues($_POST['cc_name']),
        ];
    }
}
