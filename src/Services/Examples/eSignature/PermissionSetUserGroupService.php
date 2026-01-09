<?php

namespace DocuSign\Services\Examples\eSignature;

use DateTime;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Group;
use DocuSign\eSign\Model\GroupInformation;

class PermissionSetUserGroupService
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
    public static function permissionSetUserGroup(array $args, $clientService): string
    {
        # Step 3. Construct your request body
        #ds-snippet-start:eSign25Step3
        $groups_api = $clientService->getGroupsApi();
        $group = new Group($args['permission_args']);
        $group_information = new GroupInformation(['groups' => [$group]]);
        #ds-snippet-end:eSign25Step3
        try {
            # Step 4. call the eSignature REST API
            #ds-snippet-start:eSign25Step4
            $updatedGroups = $groups_api->updateGroupsWithHttpInfo(
                $args['account_id'],
                $group_information
            );

            $remaining = $updatedGroups[2]['X-RateLimit-Remaining'] ?? null;
            $reset =  $updatedGroups[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
            #ds-snippet-end:eSign25Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $updatedGroups[0];
    }
}
