<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
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
    
    public static function permissionDelete(array $args, $clientService): string
    {
        $accounts_api = $clientService->getAccountsApi();

        try {
            # Call the eSignature REST API
            #ds-snippet-start:eSign27Step3
            $response = $accounts_api->deletePermissionProfileWithHttpInfo(
                $args['account_id'],
                $args['permission_args']['permission_profile_id']
            );

            $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $response[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
            #ds-snippet-end:eSign27Step3
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return "The permission profile has been deleted!";
    }
}
