<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\PermissionProfile;

class PermissionChangeSingleSettingService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @return PermissionProfile ['redirect_url']
     */
    public static function permissionChangeSingleSetting(array $args, $clientService): PermissionProfile
    {
        # Step 3. Construct the request body
        #ds-snippet-start:eSign26Step3
        $accounts_api = $clientService->getAccountsApi();
        $permission_profile = new PermissionProfile();
        $permission_profile->setSettings($args['permission_args']['settings']);
        #ds-snippet-end:eSign26Step3

        try {
            # Step 4. Call the eSignature REST API
            #ds-snippet-start:eSign26Step4
            $updateProfileResponse = $accounts_api->updatePermissionProfile(
                $args['account_id'],
                $args['permission_args']['permission_profile_id'],
                $permission_profile
            );
            #ds-snippet-end:eSign26Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $updateProfileResponse;
    }
}
