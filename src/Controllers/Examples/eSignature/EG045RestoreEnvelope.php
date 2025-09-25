<?php
/**
 * Example 045: Restores the deleted envelope back to sent folder
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\eSign\Client\ApiException;
use DocuSign\Services\Examples\eSignature\DeleteRestoreEnvelopeService;

class EG045RestoreEnvelope extends eSignBaseController
{
    const EG = 'eg045/RestoreEnvelope';            # reference (and url) for this example
    const FILE = __FILE__;

    const DELETE_FOLDER_ID = "recyclebin";

    const RESTORE_FOLDER_ID = "sentitems";

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
     * Check the token
     * Call the worker method
     * Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        try {
            DeleteRestoreEnvelopeService::moveEnvelopeToFolder(
                $this->clientService,
                $this->args["account_id"],
                $_SESSION["envelope_id"],
                self::RESTORE_FOLDER_ID,
                self::DELETE_FOLDER_ID,
            );

            $this->clientService->showDoneTemplate(
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ResultsPageText"],
            );
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];
    }
}
