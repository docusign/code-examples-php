<?php

namespace Example\Controllers\Examples\Admin;

include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Client\ApiClient.php";
include_once "D:\code-examples-php-private\src\docusign-orgadmin-php-client\src\Api\BulkExportsApi.php";

use DocuSign\Admin\Api\BulkExportsApi;
use Example\Controllers\AdminBaseController;

class Ex003CheckRequestStatus extends AdminBaseController
{

    const EG = 'aeg003'; # reference (and url) for this example

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
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $this->checkDsToken();

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
    }

    /**
     * Method to get a request status for bulk-export.
     * @throws \DocuSign\OrgAdmin\Client\ApiException
     */
    private function checkRequestStatus()
    {
        $apiClient = $this->clientService->getApiClient();

        $exportId = $_SESSION['export_id'];

        $bulkExportsApi = new BulkExportsApi($apiClient);

        $result = $bulkExportsApi->getUserListExport($this->organizationId, $exportId);

        return json_decode($result->__toString());
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
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
