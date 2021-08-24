<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminApiBaseController;

class EG003BulkExportUserData extends AdminApiBaseController
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

        $results = $this->getExportsData();
        $filePath = realpath($_SERVER["DOCUMENT_ROOT"]). DIRECTORY_SEPARATOR ."demo_documents" . DIRECTORY_SEPARATOR ."ExportedUserData.csv";
        if ($results) {
            $this->clientService->showDoneTemplate(
                "Bulk-export user data",
                "Bulk-export user data",
                "User data exported to $filePath<br>from UserExport:getUserListExports method:",
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
        
        $csvUri = $result->getExports()[count($result->getExports())-1]->getResults()[0]->getUrl();


        # using Guzzle https://guzzle.readthedocs.io/en/latest/index.html
        $client = new \GuzzleHttp\Client();
        $client->request('GET', $csvUri, [
            'headers' => [
                'Authorization' => "bearer {$this->args['ds_access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => "multipart/form-data; "
            ],
            'save_to' => "./demo_documents/ExportedUserData.csv"
        ]);
    

        if ($result->getExports() !== null)
            $_SESSION['export_id'] = strval($result->getExports()[0]->getId());

        return json_decode($result->__toString());
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
