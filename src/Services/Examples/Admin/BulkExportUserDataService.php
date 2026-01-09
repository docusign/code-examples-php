<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\OrganizationExportResponse;
use DocuSign\Services\AdminApiClientService;
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
        $bulkList = $bulkExportsApi->createUserListExportWithHttpInfo($organizationId, $request);
        #ds-snippet-end:Admin3Step3

        $remaining = $bulkList[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $bulkList[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        sleep(30);

        #ds-snippet-start:Admin3Step4
        $organizationExportResponse = $bulkExportsApi->getUserListExportWithHttpInfo($organizationId, $bulkList[0]["id"]);
        #ds-snippet-end:Admin3Step4

        $remaining = $organizationExportResponse[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $organizationExportResponse[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        try {
            if ($organizationExportResponse[0]["percent_completed"] < 100) {
                sleep(25);
                $organizationExportResponse = $bulkExportsApi->getUserListExportWithHttpInfo($organizationId, $bulkList[0]["id"]);
                $remaining = $organizationExportResponse[2]['X-RateLimit-Remaining'] ?? null;
                $reset = $organizationExportResponse[2]['X-RateLimit-Reset'] ?? null;

                if ($remaining !== null && $reset !== null) {
                    $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                    error_log("API calls remaining: $remaining");
                    error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
                }

                if ($organizationExportResponse[0]["percent_completed"] < 100) {
                    sleep(15);
                    $organizationExportResponse = $bulkExportsApi->getUserListExportWithHttpInfo(
                        $organizationId,
                        $bulkList["id"]
                    );
                    $remaining = $organizationExportResponse[2]['X-RateLimit-Remaining'] ?? null;
                    $reset = $organizationExportResponse[2]['X-RateLimit-Reset'] ?? null;

                    if ($remaining !== null && $reset !== null) {
                        $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                        error_log("API calls remaining: $remaining");
                        error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
                    }
                }
            }

            #ds-snippet-start:Admin3Step5
            $csvUri = $organizationExportResponse[0]->getResults()[0]->getUrl();

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

            if ($organizationExportResponse[0]->getResults() !== null) {
                $_SESSION['export_id'] = strval($organizationExportResponse[0]->getResults()[0]->getId());
            }
            $organizationExportResponse = $bulkExportsApi->getUserListExportsWithHttpInfo($organizationId);

            $remaining = $organizationExportResponse[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $organizationExportResponse[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }

            return json_decode($organizationExportResponse[0]->__toString());
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
        }
    }
}
