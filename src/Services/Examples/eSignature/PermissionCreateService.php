<?php

namespace Example\Services\Examples\eSignature;

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
     * @return PermissionProfile $results
     */
    # ***DS.snippet.0.start
    public static function permisssionCreate(array $args, $clientService): PermissionProfile
    {

        # Step 3. Construct the request body
        $accounts_api = $clientService->getAccountsApi();
        $permission_profile = new PermissionProfile($args['permission_args']);

        try {
            # Step 4. Call the eSignature REST API
            $results = $accounts_api->createPermissionProfile(
                $args['account_id'],
                $permission_profile
            );
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }
    # ***DS.snippet.0.end
}
