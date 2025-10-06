<?php
/**
 * Example 045: Restores the deleted envelope back to sent folder
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\eSign\Client\ApiException;
use DocuSign\Services\Examples\eSignature\DeleteRestoreEnvelopeService;
use DocuSign\Services\ManifestService;

class EG045RestoreEnvelope extends eSignBaseController
{
    const EG = 'eg045/RestoreEnvelope';            # reference (and url) for this example
    const FILE = __FILE__;

    const DELETE_FOLDER_ID = "recyclebin";

    const SENT_ITEMS_FOLDER_NAME = "Sent Items";

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

        $availableFolders = DeleteRestoreEnvelopeService::getFolders(
            $this->clientService,
            $this->args["account_id"]
        );

        $folders = DeleteRestoreEnvelopeService::getFolderByName(
            $availableFolders->getFolders(),
            $this->args["folder_name"]
        );

        if ($folders == null) {
            $pageText = array_values(array_filter(
                $this->codeExampleText["AdditionalPage"],
                fn($page) => $page["Name"] === "folder_does_not_exist"
            ));

            $this->clientService->showDoneTemplate(
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ExampleName"],
                ManifestService::replacePlaceholders("{0}", $this->args["folder_name"], $pageText[0]["ResultsPageText"]),
                null,
                "index.php?page=eg045/RestoreEnvelope"
            );
            exit;
        }

        try {
            DeleteRestoreEnvelopeService::moveEnvelopeToFolder(
                $this->clientService,
                $this->args["account_id"],
                $_SESSION["envelope_id"],
                $folders->getFolderId(),
                self::DELETE_FOLDER_ID,
            );

            $this->clientService->showDoneTemplate(
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ExampleName"],
                ManifestService::replacePlaceholders(
                    "{0}",
                    $_SESSION["envelope_id"],
                    ManifestService::replacePlaceholders(
                        "{1}",
                        $folders->getType(),
                        ManifestService::replacePlaceholders(
                            "{2}",
                            $this->args["folder_name"],
                            $this->codeExampleText["ResultsPageText"]
                        )
                    )
                )
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
            'folder_name' => $_POST['folder_name'] != null? $_POST['folder_name'] : self::SENT_ITEMS_FOLDER_NAME,
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];
    }
}
