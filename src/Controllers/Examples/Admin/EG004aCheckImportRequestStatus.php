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

        // Call the worker method
        $results = $this->checkRequestStatus();

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Check import request status",
                "Admin API data response output:",
                "Results from UserImport:getBulkUserImportRequest method:",
                json_encode($results)
            );
        }
    }

    /**
     * Method to check the request status of bulk-import.
     * @return string
     * @throws \DocuSign\OrgAdmin\Client\ApiException
     */
    private function checkRequestStatus(): string
    {
        // create a bulk exports api instance
        $bulkImport = $this->clientService->bulkImportsApi();

        # Step 4 start
        $importId = $_SESSION['import_id'];
        return $bulkImport->getBulkUserImportRequest(
            $this->clientService->getOrgAdminId($this->args),
            $importId
        )->__toString();
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
