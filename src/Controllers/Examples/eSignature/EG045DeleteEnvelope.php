<?php
/**
 * Example 045: Moves the envelope to deleted folder
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\eSign\Client\ApiException;
use DocuSign\Services\Examples\eSignature\DeleteRestoreEnvelopeService;
use DocuSign\Services\ManifestService;

class EG045DeleteEnvelope extends eSignBaseController
{
    const EG = 'eg045';            # reference (and url) for this example
    const FILE = __FILE__;

    const DELETE_FOLDER_ID = "recyclebin";

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
            $_SESSION["envelope_id"] = $this->args['envelope_id'];

            DeleteRestoreEnvelopeService::moveEnvelopeToFolder(
                $this->clientService,
                $this->args["account_id"],
                $this->args["envelope_id"],
                self::DELETE_FOLDER_ID,
                null
            );

            $pageText = array_values(array_filter(
                $this->codeExampleText["AdditionalPage"],
                fn($page) => $page["Name"] === "envelope_is_deleted"
            ));

            $this->clientService->showDoneTemplate(
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ExampleName"],
                ManifestService::replacePlaceholders(
                    "{0}",
                    $this->args["envelope_id"],
                    $pageText[0]["ResultsPageText"]
                ),
                null,
                "index.php?page=eg045/RestoreEnvelope"
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
            'envelope_id' => $_POST['envelope_id'],
        ];
    }
}
