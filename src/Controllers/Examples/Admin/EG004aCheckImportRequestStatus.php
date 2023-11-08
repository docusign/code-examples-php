<?php

namespace DocuSign\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Client\ApiException;
use DocuSign\Controllers\AdminApiBaseController;

class EG004ACheckImportRequestStatus extends AdminApiBaseController
{

    const EG = 'aeg004a'; # reference (and url) for this example

    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException
     */
    public function createController(): void
    {
        $this->checkDsToken();

        // We've access the example without having an import ID, therefore, let's send the user to get an import ID
        if (!isset($_SESSION['import_id'])) {
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=aeg004');
        }
        // Call the worker method
        $results = $this->checkRequestStatus();

        if ($results) {
            if ($results["status"] == "queued") {
                $this->clientService->showDoneTemplate(
                    "Request not complete",
                    "Request not complete",
                    "The request has not completed, please refresh this page",
                    null,
                    "refreshPage"
                );
            } else {
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode($results->__toString()),
                    null,
                    $this->codeExampleText["AdditionalPage"][0]["ResultsPageText"]
                );
            }
        }
    }

    /**
     * Method to check the request status of bulk-import.
     * @return object
     * @throws ApiException
     */
    private function checkRequestStatus(): object
    {
        // create a bulk exports api instance
        $bulkImport = $this->clientService->bulkImportsApi();

        #ds-snippet-start:Admin4Step4
        $importId = $_SESSION['import_id'];
        return $bulkImport->getBulkUserImportRequest(
            $this->clientService->getOrgAdminId($this->args),
            $importId
        );
        #ds-snippet-end:Admin4Step4
    }


    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $default_args = $this->getDefaultTemplateArgs();

        return $default_args;
    }
}
