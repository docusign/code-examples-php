<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Api\BulkExportsApi;
use Example\Controllers\AdminBaseController;

use function GuzzleHttp\json_decode;

class Eg002BulkExportUserData extends AdminBaseController
{
    const EG = 'aeg002'; # reference (and url) for this example

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

        $results = $this->getExportsData($this->organizationId);

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Bulk export user data",
                "Admin API data response output:",
                "Results from UserExport:getUserListExports method:",
                json_encode(json_encode($results))
            );
        }
    }

    /**
     * Method to get user bulk-exports from your organization.
     * @throws \DocuSign\OrgAdmin\Client\ApiException
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
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];
    }
}
