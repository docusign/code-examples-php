<?php

namespace Example\Services\Examples\Rooms;

use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\FormGroupFormToAssign;

class AssignFormToFormGroupService
{
    /**
     * 1. Construct request body
     * 2. Assign form to form group using SDK.
     *
     * @param  $args array
     * @param $clientService
     * @return FormGroupFormToAssign
     */
    public static function assignFormToFormGroup(array $args, $clientService): FormGroupFormToAssign
    {
        #ds-snippet-start:Rooms9Step5
        $form_group_form_to_assign = new FormGroupFormToAssign(['form_id' => $args['form_id']]);
        #ds-snippet-end:Rooms9Step5

        try {
            #ds-snippet-start:Rooms9Step6
            $form_api = $clientService->getFromGroupsApi();
            $formGroupResult = $form_api->assignFormGroupForm(
                $args['form_group_id'],
                $args["account_id"],
                $form_group_form_to_assign
            );
            #ds-snippet-end:Rooms9Step6
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $formGroupResult;
    }

    #ds-snippet-start:Rooms9Step3

    /**
     * Get available forms
     *
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getForms($routerService, $clientService, $args, $eg): array
    {
        $forms = [];
        $libraries = AssignFormToFormGroupService::getFormLibraries(
            $routerService,
            $clientService,
            $args,
            $eg
        );
        if (count($libraries)) {
            $forms = AssignFormToFormGroupService::getFormLibraryForms(
                $libraries[0]['forms_library_id'],
                $routerService,
                $clientService,
                $args,
                $eg
            );
        }
        return $forms;
    }

    /**
     * Get Form Libraries
     *
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getFormLibraries($routerService, $clientService, $args, $eg): array
    {
        $minimum_buffer_min = 3;
        if ($routerService->dsTokenOk($minimum_buffer_min)) {
            return $clientService->getFormLibraries($args);
        } else {
            $clientService->needToReAuth($eg);
        }
    }

    /**
     * Get available Forms
     *
     * @param string $libraryID
     * @param $routerService
     * @param $clientService
     * @param $args
     * @param $eg
     * @return array
     */
    public static function getFormLibraryForms(string $libraryID, $routerService, $clientService, $args, $eg): array
    {
        $minimum_buffer_min = 3;
        if ($routerService->dsTokenOk($minimum_buffer_min)) {
            return $clientService->getFormLibraryForms($libraryID, $args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
    #ds-snippet-end:Rooms9Step3

    #ds-snippet-start:Rooms9Step4
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
        $minimum_buffer_min = 3;
        $formGroups = [];
        if ($routerService->dsTokenOk($minimum_buffer_min)) {
            $formGroups = $clientService->getFormGroups($args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
        return $formGroups;
    }
    #ds-snippet-end:Rooms9Step4
}
