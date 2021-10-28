<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Api\BulkExportsApi;
use DocuSign\Admin\Client\ApiException;
use Example\Services\AdminApiClientService;

class CheckRequestStatusService
{
    /**
     * Method to get a request status for bulk-export.
     * @throws ApiException
     */
    public static function checkRequestStatus(
        AdminApiClientService $clientService,
        string $organizationId,
        string $exportId
    ) {
        $apiClient = $clientService->getApiClient();

        $bulkExportsApi = new BulkExportsApi($apiClient);

        $result = $bulkExportsApi->getUserListExport($organizationId, $exportId);

        return json_decode($result->__toString());
    }
}
