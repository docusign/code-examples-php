<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
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
        $response = $bulkImport->getBulkUserImportRequestWithHttpInfo(
            $organizationId,
            $importId
        );

        $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        if ($response[0]->getStatus()== "queued") {
            return "Please refresh the page";
        } else {
            unset($_SESSION['import_id']);
            return $response[0]->__toString();
        }
        
        # Step 4 end
    }
}
