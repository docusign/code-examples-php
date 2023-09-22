<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;

class PermissionDeleteService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @return string
     */
    # ***DS.snippet.0.start
    public static function permissionDelete(array $args, $clientService): string
    {
        $accounts_api = $clientService->getAccountsApi();

        try {
            # Step 3. call the eSignature REST API
            $accounts_api->deletePermissionProfile(
                $args['account_id'],
                $args['permission_args']['permission_profile_id']
            );
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return "The permission profile has been deleted!";
    }
    # ***DS.snippet.0.end
}
