<?php

namespace DocuSign\Services\Examples\Rooms;

use DocuSign\Rooms\Client\ApiException;

class GrantOfficeAccessToFormGroupService
{
    /**
     *  Grant office access to a form group using SDK.
     *
     * @param  $args array
     * @param $clientService
     * @return void
     */
    public static function grantOfficeAccessToFormGroup(array $args, $clientService): void
    {
        try {
            # Step 5 Start
            $form_api = $clientService->getFromGroupsApi();
            $form_api->grantOfficeAccessToFormGroup($args['form_group_id'], $args['office_id'], $args["account_id"]);
            # Step 5 End
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }
    }

    /**
     * Get available offices
     *
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getOffices($routerService, $clientService, $args, $eg): array
    {
        $offices = [];
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            $offices = $clientService->getOffices($args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
        return $offices;
    }

    /**
     * Get available form groups
     *
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getFormGroups($routerService, $clientService, $args, $eg): array
    {
        $formGroups = [];
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            $formGroups = $clientService->getFormGroups($args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
        return $formGroups;
    }
}
