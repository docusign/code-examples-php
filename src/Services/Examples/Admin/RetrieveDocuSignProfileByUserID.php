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
        $usersApi = $clientService->getUsersApi();

        $usersResponse = $usersApi->getUserDSProfile($organizationId, $userId);

        return json_decode((string) $usersResponse, true);
    }
}
