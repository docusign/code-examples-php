<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminApiBaseController;

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
     * @throws \DocuSign\OrgAdmin\Client\ApiException
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
                    Null,
                    "refreshPage"
                );
                
            } else {
                $this->clientService->showDoneTemplate(
                    "Check import request status",
                    "Admin API data response output:",
                    "Results from UserImport:getBulkUserImportRequest method:",
                    json_encode($results->__toString())
                );

            }
        }
    }

    /**
     * Method to check the request status of bulk-import.
     * @return object
     * @throws \DocuSign\OrgAdmin\Client\ApiException
     */
    private function checkRequestStatus(): object
    {
        // create a bulk exports api instance
        $bulkImport = $this->clientService->bulkImportsApi();

        # Step 4 start
        $importId = $_SESSION['import_id'];
        return $bulkImport->getBulkUserImportRequest(
            $this->clientService->getOrgAdminId($this->args),
            $importId
        );
        # Step 4 end
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
