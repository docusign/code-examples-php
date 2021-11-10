<?php

namespace Example\Services\Examples\eSignature;

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
    # ***DS.snippet.0.start
    public static function permissionChangeSingleSetting(array $args, $clientService): PermissionProfile
    {
        # Step 3. Construct the request body
        $accounts_api = $clientService->getAccountsApi();
        $permission_profile = new PermissionProfile();
        $permission_profile->setSettings($args['permission_args']['settings']);

        try {
            # Step 4. Call the eSignature REST API
            $updateProfileResponse = $accounts_api->updatePermissionProfile(
                $args['account_id'],
                $args['permission_args']['permission_profile_id'],
                $permission_profile
            );
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $updateProfileResponse;
    }
    # ***DS.snippet.0.end
}
