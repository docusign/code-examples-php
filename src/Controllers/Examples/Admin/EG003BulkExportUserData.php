<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Model\OrganizationExportResponse;
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
        $filePath = realpath($_SERVER["DOCUMENT_ROOT"]). DIRECTORY_SEPARATOR ."public" . DIRECTORY_SEPARATOR ."demo_documents" . DIRECTORY_SEPARATOR ."ExportedUserData.csv";
        if ($results) {
            $this->clientService->showDoneTemplate(
                "Bulk export user data",
                "Bulk export user data",
                "User data exported to $filePath<br>from UserExport:getUserListExport method:",
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

        # Step 3 start
        $bulkExportsApi = $this->clientService->bulkExportsAPI();
        $request = new OrganizationExportResponse();
        $request->setType("organization_memberships_export");
        $bulkList = $bulkExportsApi->createUserListExport($organizationId, $request);        
        # Step 3 end

        sleep(15);
        
        # Step 4 start
        $result = $bulkExportsApi->getUserListExport($organizationId, $bulkList["id"]);
        # Step 4 end

        # Step 5 start
        $csvUri = $result->getResults()[0]->getUrl();

        $client = new \GuzzleHttp\Client();
        $client->request('GET', $csvUri, [
            'headers' => [
                'Authorization' => "bearer {$this->args['ds_access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => "multipart/form-data; "
            ],
            'save_to' => "./demo_documents/ExportedUserData.csv"
        ]);
        # Step 5 end

        if ($result->getResults() !== null) {

        
            $_SESSION['export_id'] = strval($result->getResults()[0]->getId());
        }
        $result = $bulkExportsApi->getUserListExports($organizationId);
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
