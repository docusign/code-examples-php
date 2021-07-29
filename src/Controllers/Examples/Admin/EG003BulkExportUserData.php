<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminBaseController;

use function GuzzleHttp\json_decode;

class EG003BulkExportUserData extends AdminBaseController
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
    private function getExportsData()
    {
        $organizationId = $this->clientService->getOrgAdminId($this->args);
        $bulkExportsApi = $this->clientService->bulkExportsAPI();
        $result = $bulkExportsApi->getUserListExports($organizationId);
        var_dump($result);
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
