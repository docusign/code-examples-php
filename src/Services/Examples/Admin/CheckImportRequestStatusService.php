<?php

namespace DocuSign\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Services\AdminApiClientService;

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
        $response = $bulkImport->getBulkUserImportRequest(
            $organizationId,
            $importId
        );

        if ($response->getStatus()== "queued") {
            return "Please refresh the page";
        } else {
            unset($_SESSION['import_id']);
            return $response->__toString();
        }
        
        # Step 4 end
    }
}
