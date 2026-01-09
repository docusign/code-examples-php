<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Services\AdminApiClientService;

class RetrieveDocuSignProfileByUserId
{
    /**
     * Get a DocuSign profile by the user ID
     * @param string $organizationId
     * @param string $userId
     * @param AdminApiClientService $clientService
     * @return array
     * @throws ApiException
     */
    public static function getDocuSignProfileByUserId(
        string $organizationId,
        string $userId,
        AdminApiClientService $clientService
    ): array {
        #ds-snippet-start:Admin7Step3
        $usersApi = $clientService->getUsersApi();

        $usersResponse = $usersApi->getUserDSProfileWithHttpInfo($organizationId, $userId);

        $remaining = $usersResponse[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $usersResponse[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }
        #ds-snippet-end:Admin7Step3

        return json_decode((string) $usersResponse[0], true);
    }
}
