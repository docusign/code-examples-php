<?php

namespace Example\Services\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\AddUserResponse;
use DocuSign\Admin\Model\DSGroupRequest;
use DocuSign\Admin\Model\NewMultiProductUserAddRequest;
use DocuSign\Admin\Model\ProductPermissionProfileRequest;
use Example\Services\AdminApiClientService;

class CreateActiveCLMESignUserService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param AdminApiClientService $clientService
     * @param array $arguments
     * @param string $organizationId
     * @return AddUserResponse AddUserResponse
     */
    public static function createActiveCLMESignUser(
        AdminApiClientService $clientService,
        array $arguments,
        string $organizationId
    ): AddUserResponse {
# Step 5 Start
        $admin_api = $clientService->getUsersApi();

        $eSignProfile = new ProductPermissionProfileRequest(
            [
                "permission_profile_id" => $arguments["esign_permission_profile_id"],
                "product_id" => $arguments["esign_product_id"]
            ]
        );

        $clmProfile = new ProductPermissionProfileRequest(
            [
                "permission_profile_id" => $arguments["clm_permission_profile_id"],
                "product_id" => $arguments["clm_product_id"]
            ]
        );

        $dsGroups = new DSGroupRequest(
            [
                'ds_group_id' => $arguments["group_id"]
            ]
        );

        $request = new NewMultiProductUserAddRequest(
            [
                'user_name' => $arguments["user_id"],
                'first_name' => $arguments["first_name"],
                'last_name' => $arguments["last_name"],
                'auto_activate_memberships' => true,
            ]
        );
        $request->setProductPermissionProfiles([$clmProfile, $eSignProfile]);
        $request->setEmail($arguments["email"]);
        $request->setDsGroups([$dsGroups]);
        # Step 5 end
        try {
            # Step 6 start
            $results = $admin_api->addOrUpdateUser($organizationId, $arguments["account_id"], $request);
            # Step 6 end

        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
        }
        return $results;
    }
}
