<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminBaseController;
use DocuSign\OrgAdmin\Api\BulkImportsApi;

class Ex005CheckImportRequestStatus extends AdminBaseController
{

    const EG = 'aeg005'; # reference (and url) for this example

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
        $bulkImportsApi = new BulkImportsApi($this->clientService->getApiClient());

        $importId = $_SESSION['import_id'];

        return $bulkImportsApi->getBulkUserImportRequest($this->organizationId, $importId)->__toString();
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
        ];
    }
}
