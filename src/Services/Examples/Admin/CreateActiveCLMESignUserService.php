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
        #ds-snippet-start:Admin2Step5
        $userAPI = $clientService->getUsersApi();

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
                'default_account_id' => $arguments["account_id"],
                'user_name' => $arguments["user_id"],
                'first_name' => $arguments["first_name"],
                'last_name' => $arguments["last_name"],
                'auto_activate_memberships' => true,
            ]
        );
        $request->setProductPermissionProfiles([$clmProfile, $eSignProfile]);
        $request->setEmail($arguments["email"]);
        $request->setDsGroups([$dsGroups]);
        #ds-snippet-end:Admin2Step5
        try {
            #ds-snippet-start:Admin2Step6
            $addUserResponse = $userAPI->addOrUpdateUser($organizationId, $arguments["account_id"], $request);
            #ds-snippet-end:Admin2Step6

        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
        }
        return $addUserResponse;
    }
}
