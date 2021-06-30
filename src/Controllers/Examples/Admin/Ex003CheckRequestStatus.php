<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Api\BulkExportsApi;
use Example\Controllers\AdminBaseController;
use DocuSign\Monitor\Client\ApiException;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;

use function GuzzleHttp\json_decode;

class Ex003CheckRequestStatus extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg003";  # reference (and url) for this example

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
                    "Check request status",
                    "Admin API data response output:",
                    "Results from UserExport:getUserListExport method:",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Method to get a request status for bulk-export.
     * @throws ApiException for API problems.
     */
    private function checkRequestStatus()
    {
        $apiClient = $this->clientService->getApiClient();

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $exportId = $_SESSION['export_id'];
        
        $bulkExportsApi = new BulkExportsApi($apiClient);

        $result = $bulkExportsApi->getUserListExport($organizationId, $exportId);

        return json_decode($result->__toString());
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
            'ds_access_token' => $_SESSION['ds_access_token']
        ];

        return $args;
    }
}
