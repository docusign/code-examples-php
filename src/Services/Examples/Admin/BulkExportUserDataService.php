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
        #ds-snippet-start:Admin3Step3
        $bulkExportsApi = $clientService->bulkExportsAPI();
        $request = new OrganizationExportResponse();
        $request->setType("organization_memberships_export");
        $bulkList = $bulkExportsApi->createUserListExport($organizationId, $request);
        #ds-snippet-end:Admin3Step3

        sleep(30);

        #ds-snippet-start:Admin3Step4
        $organizationExportResponse = $bulkExportsApi->getUserListExport($organizationId, $bulkList["id"]);
        #ds-snippet-end:Admin3Step4

        try {
            if ($organizationExportResponse["percent_completed"] < 100) {
                sleep(25);
                $organizationExportResponse = $bulkExportsApi->getUserListExport($organizationId, $bulkList["id"]);

                if ($organizationExportResponse["percent_completed"] < 100) {
                    sleep(15);
                    $organizationExportResponse = $bulkExportsApi->getUserListExport($organizationId, $bulkList["id"]);
                }
            }

            #ds-snippet-start:Admin3Step5
            $csvUri = $organizationExportResponse->getResults()[0]->getUrl();

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
            #ds-snippet-end:Admin3Step5

            if ($organizationExportResponse->getResults() !== null) {
                $_SESSION['export_id'] = strval($organizationExportResponse->getResults()[0]->getId());
            }
            $organizationExportResponse = $bulkExportsApi->getUserListExports($organizationId);
            return json_decode($organizationExportResponse->__toString());
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
        }
    }
}
