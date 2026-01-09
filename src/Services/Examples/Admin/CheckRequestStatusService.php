<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Services\AdminApiClientService;

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
        $bulkExportsApi = $clientService->bulkExportsAPI();

        $organizationExportResponse = $bulkExportsApi->getUserListExportWithHttpInfo($organizationId, $exportId);

        $remaining = $organizationExportResponse[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $organizationExportResponse[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        return json_decode($organizationExportResponse[0]->__toString());
    }
}
