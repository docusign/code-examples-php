<?php

namespace DocuSign\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\ProductPermissionProfileRequest;
use DocuSign\Admin\Model\UserProductPermissionProfilesRequest;
use DocuSign\Admin\Model\UserProductPermissionProfilesResponse;
use DocuSign\Services\AdminApiClientService;
use DocuSign\Admin\Api\ProductPermissionProfilesApi;

class UpdateUserProductPermissionProfileByEmailService
{
    /**
     * Method to update user product permission profile by email.
     * @param ProductPermissionProfilesApi $productPermissionProfilesApi
     * @param string $organizationId
     * @param string $accountId
     * @param string $emailAddress
     * @param string $productId
     * @param string $permissionProfileId
     * @return UserProductPermissionProfilesResponse
     * @throws ApiException
     */
    public static function updateUserProductPermissionProfile(
        ProductPermissionProfilesApi $productPermissionProfilesApi,
        string $organizationId,
        string $accountId,
        string $emailAddress,
        string $productId,
        string $permissionProfileId
    ): UserProductPermissionProfilesResponse {
        #ds-snippet-start:Admin8Step3
        $userProductPermissionProfilesRequest = new UserProductPermissionProfilesRequest([
            'email' => $emailAddress,
            'product_permission_profiles' => [new ProductPermissionProfileRequest([
                'product_id' => $productId,
                'permission_profile_id' => $permissionProfileId,
            ])],
        ]);
        #ds-snippet-end:Admin8Step3

        #ds-snippet-start:Admin8Step4
        return $productPermissionProfilesApi->addUserProductPermissionProfilesByEmail(
            $organizationId,
            $accountId,
            $userProductPermissionProfilesRequest
        );
        #ds-snippet-end:Admin8Step4
    }
}
