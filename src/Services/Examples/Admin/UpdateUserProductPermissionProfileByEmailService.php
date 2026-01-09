<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
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
        $response = $productPermissionProfilesApi->addUserProductPermissionProfilesByEmailWithHttpInfo(
            $organizationId,
            $accountId,
            $userProductPermissionProfilesRequest
        );

        $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        return $response[0];
        #ds-snippet-end:Admin8Step4
    }
}
