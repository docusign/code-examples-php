<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
use DocuSign\Admin\Api\UsersApi\GetUserDSProfilesByEmailOptions;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Services\AdminApiClientService;

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
        #ds-snippet-start:Admin6Step3
        $usersApi = $clientService->getUsersApi();

        $userOptions = new GetUserDSProfilesByEmailOptions();
        $userOptions->setEmail($email);

        $usersResponse = $usersApi->getUserDSProfilesByEmailWithHttpInfo($organizationId, $userOptions);

        $remaining = $usersResponse[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $usersResponse[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }
        #ds-snippet-end:Admin6Step3
        return json_decode((string) $usersResponse[0], true);
    }
}
