<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\OrganizationExportResponse;
use Example\Services\AdminApiClientService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BulkExportUserDataService
{
    /**
     * Method to get user bulk-exports from your organization.
     * @param AdminApiClientService $clientService
     * @param array $arguments
     * @param string $organizationId
     * @return mixed
     * @throws ApiException
     * @throws GuzzleException
     */
    public static function getExportsData(
        AdminApiClientService $clientService,
        array $arguments,
        string $organizationId
    ) {
        # Step 3 start
        $bulkExportsApi = $clientService->bulkExportsAPI();
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

        $client = new Client();
        $client->request(
            'GET',
            $csvUri,
            [
                'headers' => [
                    'Authorization' => "bearer {$arguments['ds_access_token']}",
                    'Accept' => 'application/json',
                    'Content-Type' => "multipart/form-data; "
                ],
                'save_to' => "./demo_documents/ExportedUserData.csv"
            ]
        );
        # Step 5 end

        if ($result->getResults() !== null) {
            $_SESSION['export_id'] = strval($result->getResults()[0]->getId());
        }
        $result = $bulkExportsApi->getUserListExports($organizationId);
        return json_decode($result->__toString());
    }
}
