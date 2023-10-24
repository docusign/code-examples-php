<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\PermissionProfile;

class PermissionCreateService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param array $args
     * @param $clientService
     * @return PermissionProfile $permissionProfile
     */
    #
    public static function permisssionCreate(array $args, $clientService): PermissionProfile
    {

        # Step 3. Construct the request body
        #ds-snippet-start:eSign24Step3
        $accounts_api = $clientService->getAccountsApi();
        $permission_profile = new PermissionProfile($args['permission_args']);
        #ds-snippet-end:eSign24Step3
        try {
            # Step 4. Call the eSignature REST API
            #ds-snippet-start:eSign24Step4
            $permissionProfile = $accounts_api->createPermissionProfile(
                $args['account_id'],
                $permission_profile
            );
            #ds-snippet-end:eSign24Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $permissionProfile;
    }
    #
}
