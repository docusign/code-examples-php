<?php

namespace DocuSign\Services\Examples\Admin;

use DateTime;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\UserProductProfileDeleteRequest;
use DocuSign\Admin\Model\RemoveUserProductsResponse;
use DocuSign\Services\AdminApiClientService;
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
        $response = $productPermissionProfilesApi->removeUserProductPermissionWithHttpInfo(
            $organizationId,
            $accountId,
            $userProductProfileDeleteRequest
        );

        $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
        $reset = $response[2]['X-RateLimit-Reset'] ?? null;

        if ($remaining !== null && $reset !== null) {
            $resetInstant = (new DateTime())->setTimestamp((int)$reset);
            error_log("API calls remaining: $remaining");
            error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
        }

        return $response[0];
        #ds-snippet-end:Admin9Step5
    }
}
