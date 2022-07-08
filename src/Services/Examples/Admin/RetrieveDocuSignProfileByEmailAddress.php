<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Api\UsersApi\GetUserDSProfilesByEmailOptions;
use DocuSign\Admin\Client\ApiException;
use Example\Services\AdminApiClientService;

class RetrieveDocuSignProfileByEmailAddress
{
    /**
     * Get a DocuSign profile by the email address
     * @param string $organizationId
     * @param string $email
     * @param AdminApiClientService $clientService
     * @return array
     * @throws ApiException
     */
    public static function getDocuSignProfileByEmailAddress(
        string $organizationId,
        string $email,
        AdminApiClientService $clientService
    ): array {
        $usersApi = $clientService->getUsersApi();

        $userOptions = new GetUserDSProfilesByEmailOptions();
        $userOptions->setEmail($email);

        $usersResponse = $usersApi->getUserDSProfilesByEmail($organizationId, $userOptions);

        return json_decode((string) $usersResponse, true);
    }
}
