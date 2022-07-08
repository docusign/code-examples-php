<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\UserProductProfileDeleteRequest;
use DocuSign\Admin\Model\RemoveUserProductsResponse;
use Example\Services\AdminApiClientService;
use DocuSign\Admin\Api\ProductPermissionProfilesApi;

class DeleteUserProductPermissionProfileByIdService
{
    /**
     * Method to delete user product permission profile by email.
     * @param ProductPermissionProfilesApi $productPermissionProfilesApi
     * @param string $organizationId
     * @param string $accountId
     * @param string $emailAddress
     * @param string $productId
     * @return RemoveUserProductsResponse
     * @throws ApiException
     */
    public static function deleteUserProductPermissionProfile(
        ProductPermissionProfilesApi $productPermissionProfilesApi,
        string $organizationId,
        string $accountId,
        string $emailAddress,
        string $productId
    ): RemoveUserProductsResponse {
        $userProductProfileDeleteRequest = new UserProductProfileDeleteRequest([
            'user_email' => $emailAddress,
            'product_ids' => [$productId],
        ]);

        return $productPermissionProfilesApi->removeUserProductPermission(
            $organizationId,
            $accountId,
            $userProductProfileDeleteRequest
        );
    }
}
