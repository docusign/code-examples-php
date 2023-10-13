<?php

namespace Example\Services\Examples\Rooms;

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
            #ds-snippet-start:Rooms8Step5
            $form_api = $clientService->getFromGroupsApi();
            $form_api->grantOfficeAccessToFormGroup($args['form_group_id'], $args['office_id'], $args["account_id"]);
            #ds-snippet-end:Rooms8Step5
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
        if ($routerService->ds_token_ok($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
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
        if ($routerService->ds_token_ok($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            $formGroups = $clientService->getFormGroups($args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
        return $formGroups;
    }
}
