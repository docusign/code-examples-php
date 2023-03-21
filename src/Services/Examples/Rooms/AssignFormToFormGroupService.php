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
        # Step 5 Start
        $form_group_form_to_assign = new FormGroupFormToAssign(['form_id' => $args['form_id']]);
        # Step 5 End

        try {
            # Step 6 Start
            $form_api = $clientService->getFromGroupsApi();
            $formGroupResult = $form_api->assignFormGroupForm($args['form_group_id'], $args["account_id"], $form_group_form_to_assign);
            # Step 6 End
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $formGroupResult;
    }

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
        $minimumBufferMin = 3;
        $formGroups = [];
        if ($routerService->ds_token_ok($minimumBufferMin)) {
            $formGroups = $clientService->getFormGroups($args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
        return $formGroups;
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
        $minimumBufferMin = 3;
        if ($routerService->ds_token_ok($minimumBufferMin)) {
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
        $minimumBufferMin = 3;
        if ($routerService->ds_token_ok($minimumBufferMin)) {
            return $clientService->getFormLibraryForms($libraryID, $args['account_id']);
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
