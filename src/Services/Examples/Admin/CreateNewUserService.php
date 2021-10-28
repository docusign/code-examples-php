<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Api\UsersApi;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\NewUserRequestAccountProperties;
use DocuSign\Admin\Model\NewUserResponse;
use DocuSign\Admin\Model\GroupRequest;
use DocuSign\Admin\Model\PermissionProfileRequest;
use Example\Services\AdminApiClientService;
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
        # Step 3 start
        $usersApi = $clientService->getUsersApi();
        $accountId = $_SESSION['ds_account_id'];
        $permissionProfile = new PermissionProfileRequest([
            'id' => $userData['permission_profile_id']
        ]);

        $group = new GroupRequest([
            'id' => (int) $userData['group_id']
        ]);

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
        # Step 3 end

        # Step 4 start
        return $usersApi->createUser($organizationId, $request);
        # Step 4 end
    }
}
