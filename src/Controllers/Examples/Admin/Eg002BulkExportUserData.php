<?php

namespace Example\Controllers\Examples\Admin;
use DocuSign\OrgAdmin\Api\BulkExportsApi;
use Example\Controllers\AdminBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;

use function GuzzleHttp\json_decode;

class Eg002BulkExportUserData extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg002";  # reference (and url) for this example

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
        $accessToken =  $_SESSION['ds_access_token'];
        $minimum_buffer_min = 3;

        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->getExportsData($this->organizationId, $accessToken);

            if ($results) {
                $this->clientService->showDoneTemplate(
                    "Bulk export user data",
                    "Admin API data response output:",
                    "Results from UserExport:getUserListExports method:",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Method to get user bulk-exports from your organization.
     * @throws ApiException for API problems.
     */
    private function getExportsData($organizationId)
    {
        $apiClient = $this->clientService->getApiClient();

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $bulkExportsApi = new BulkExportsApi($apiClient);

        $result = $bulkExportsApi->getUserListExports($organizationId);

        $_SESSION['export_id'] = strval($result->getExports()[0]->getId());

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
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}
