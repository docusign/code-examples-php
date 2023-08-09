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
        #ds-snippet-start:Admin9Step4
        $userProductProfileDeleteRequest = new UserProductProfileDeleteRequest([
            'user_email' => $emailAddress,
            'product_ids' => [$productId],
        ]);
        #ds-snippet-end:Admin9Step4

        #ds-snippet-start:Admin9Step5
        return $productPermissionProfilesApi->removeUserProductPermission(
            $organizationId,
            $accountId,
            $userProductProfileDeleteRequest
        );
        #ds-snippet-end:Admin9Step5
    }
}
