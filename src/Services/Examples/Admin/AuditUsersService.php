<?php

namespace DocuSign\Services\Examples\Admin;

use DocuSign\Admin\Api\UsersApi\GetUserProfilesOptions;
use DocuSign\Admin\Api\UsersApi\GetUsersOptions;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\UsersDrilldownResponse;
use DocuSign\Services\AdminApiClientService;
use DocuSign\Services\ManifestService;

class AuditUsersService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * @param AdminApiClientService $clientService
     * @param array $arguments
     * @param string $organizationId
     * @return array ['redirect_url']
     */
    public static function auditUsers(
        AdminApiClientService $clientService,
        array $arguments,
        string $organizationId
    ): array {
        # Step 5a start
        $usersInformation = [];
        $userDrilldownResponse = new UsersDrilldownResponse();
        # Step 5a end

        $admin_api = $clientService->getUsersApi();

        # Here we set the from_date to filter envelopes for the last 10 days
        # Use ISO 8601 date format

        # Step 3 start
        $options = new GetUsersOptions();
        $options->setAccountId($arguments["account_id"]);
        $from_date = date("c", (time() - (10 * 24 * 60 * 60)));
        $options->setLastModifiedSince($from_date);

        try {
            $modifiedUsers = $admin_api->getUsers($organizationId, $options);
            # Step 3 end

            # Step 4 start
            foreach ($modifiedUsers["users"] as $user) {
                $profileOptions = new GetUserProfilesOptions();
                $profileOptions->setEmail($user["email"]);
                # Step 4 end

                # Step 5b start
                $res = $admin_api->getUserProfiles($organizationId, $profileOptions);
                $userDrilldownResponse->setUsers($res->getUsers());
                $decoded = json_decode((string)$userDrilldownResponse, true);
                array_push($usersInformation, $decoded["users"]);
                # Step 5b end
            }
        } catch (ApiException $e) {
            $GLOBALS['twig']->display(
                'error.html',
                [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'common_texts' => ManifestService::getCommonTexts()
                ]
            );
            exit;
        }

        return $usersInformation;
    }
}
