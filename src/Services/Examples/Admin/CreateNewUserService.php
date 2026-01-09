<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\NewUserRequestAccountProperties;
use DocuSign\Admin\Model\NewUserResponse;
use DocuSign\Admin\Model\GroupRequest;
use DocuSign\Admin\Model\PermissionProfileRequest;
use DocuSign\Services\AdminApiClientService;
use DocuSign\Admin\Model\NewUserRequest as GlobalNewUserRequest;

class CreateNewUserService
{
    /**
     * Method to add a new user to your organization.
     * @param string $organizationId
     * @param array $userData
     * @param AdminApiClientService $clientService
     * @return NewUserResponse
     * @throws ApiException
     */
    public static function addActiveUser(
        string $organizationId,
        array $userData,
        AdminApiClientService $clientService
    ): NewUserResponse {
        $usersApi = $clientService->getUsersApi();
        $accountId = $_SESSION['ds_account_id'];
        #ds-snippet-start:Admin1Step3
        $permissionProfile = new PermissionProfileRequest([
            'id' => $userData['permission_profile_id']
        ]);
        #ds-snippet-end:Admin1Step3

        #ds-snippet-start:Admin1Step4
        $group = new GroupRequest([
            'id' => (int) $userData['group_id']
        ]);
        #ds-snippet-end:Admin1Step4

        #ds-snippet-start:Admin1Step5
        $accountInfo = new NewUserRequestAccountProperties([
            'id' => $accountId,
            'permission_profile' => $permissionProfile,
            'groups' => [ $group ]
        ]);

        $request = new GlobalNewUserRequest([
            'user_name' => $userData['Name'],
            'first_name' => $userData['FirstName'],
            'last_name' => $userData['LastName'],
            'email' => $userData['Email'],
            'default_account_id' => $accountId,
            'accounts' => array($accountInfo),
            'auto_activate_memberships' => true
        ]);
        #ds-snippet-end:Admin1Step5

        #ds-snippet-start:Admin1Step6
        $response = $usersApi->createUserWithHttpInfo($organizationId, $request);
        $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }
        return $response[0];
        #ds-snippet-end:Admin1Step6
    }
}
