<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Services\AdminApiClientService;

class CheckImportRequestStatusService
{
    /**
     * Method to check the request status of bulk-import.
     * @param AdminApiClientService $clientService
     * @return string
     * @throws ApiException
     */
    public static function checkRequestStatus(
        AdminApiClientService $clientService,
        string $organizationId,
        string $importId
    ): string {
        // create a bulk exports api instance
        $bulkImport = $clientService->bulkImportsApi();

        # Step 4 start
        return $bulkImport->getBulkUserImportRequest(
            $organizationId,
            $importId
        )->__toString();
        # Step 4 end
    }
}
