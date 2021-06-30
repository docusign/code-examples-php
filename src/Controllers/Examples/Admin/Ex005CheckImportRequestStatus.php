<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminBaseController;
use Example\Services\RouterService;
use DocuSign\Monitor\Client\ApiException;
use DocuSign\OrgAdmin\Api\BulkImportsApi;
use Example\Services\AdminApiClientService;

class Ex005CheckImportRequestStatus extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg005";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;

        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
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
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Method to check the request status of bulk-import.
     * @return string
     * @throws ApiException for API problems.
     */
    private function checkRequestStatus(): string
    {
        // create a bulk exports api instance
        $bulkImportsApi = new BulkImportsApi($this->clientService->getApiClient());

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $importId = $_SESSION['import_id'];

        return $bulkImportsApi->getBulkUserImportRequest($organizationId, $importId)->__toString();
    }

    /**
     * Get specific template arguments
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}
