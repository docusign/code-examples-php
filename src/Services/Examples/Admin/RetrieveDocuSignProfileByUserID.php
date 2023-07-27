<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Services\AdminApiClientService;

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

        $usersResponse = $usersApi->getUserDSProfile($organizationId, $userId);
        #ds-snippet-end:Admin7Step3

        return json_decode((string) $usersResponse, true);
    }
}
