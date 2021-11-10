<?php

namespace Example\Services\Examples\eSignature;

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
    # ***DS.snippet.0.start
    public static function permissionSetUserGroup(array $args, $clientService): string
    {
        # Step 3. Construct your request body
        $groups_api = $clientService->getGroupsApi();
        $group = new Group($args['permission_args']);
        $group_information = new GroupInformation(['groups' => [$group]]);

        try {
            # Step 4. call the eSignature REST API
            $updatedGroups = $groups_api->updateGroups(
                $args['account_id'],
                $group_information
            );
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $updatedGroups;
    }
    # ***DS.snippet.0.end
}
