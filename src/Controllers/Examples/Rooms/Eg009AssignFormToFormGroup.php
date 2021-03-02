<?php

namespace Example\Controllers\Examples\Rooms;

use DocuSign\Rooms\Client\ApiException;
use DocuSign\Rooms\Model\FormGroupFormToAssign;
use Example\Controllers\RoomsApiBaseController;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;

class Eg009AssignFormToFormGroup extends RoomsApiBaseController
{
    private RoomsApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "reg009";  # reference (and url) for this example

    /**
     * 1. Get available forms
     * 2. Get available form groups
     * 3. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new RoomsApiClientService($this->args);
        $this->routerService = new RouterService();

        # Step 3 Start
        $forms = $this->getForms();
        # Step 3 End

        # Step 4 Start
        $formGroups = $this->getFormGroups();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, null, $forms, null, $formGroups);
        # Step 4 End
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Render request results
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $this->worker($this->args);
            $this->clientService->showDoneTemplate(
                "Assign a form to a form group",
                "Assign a form to a form group",
                "Results from the FormGroups::AssignFormGroupForm method".
                "<pre>Code: 204<br />Description: Office was successfully assigned to the form group</pre>"
            );
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * 1. Construct request body
     * 2. Assign form to form group using SDK.
     *
     * @param  $args array
     * @return FormGroupFormToAssign
     */
    public function worker(array $args): FormGroupFormToAssign
    {
        # Step 5 Start
        $form_group_form_to_assign = new FormGroupFormToAssign(['form_id' => $args['form_id']]);
        # Step 5 End

        
        try {
            # Step 6 Start
            $form_api = $this->clientService->getFromGroupsApi();
            $result = $form_api->assignFormGroupForm($args['form_group_id'], $args["account_id"], $form_group_form_to_assign);
            # Step 6 End
        } catch (ApiException $e) {
            error_log($e);
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        
       return $result;
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $form_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['form_id']);
        $form_group_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['form_group_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'form_id' => $form_id,
            'form_group_id' => $form_group_id
        ];
    }

    /**
     * Get available forms
     *
     * @return array
     */
    private function getForms(): array
    {
        $forms = [];
        $libraries = $this->getFormLibraries();
        if (count($libraries)) {
            $forms = $this->getFormLibraryForms($libraries[0]['forms_library_id']);
        }
        return $forms;
    }

    /**
     * Get available form groups
     *
     * @return array
     */
    private function getFormGroups(): array
    {
        $minimum_buffer_min = 3;
        $formGroups = [];
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $formGroups = $this->clientService->getFormGroups($this->args['account_id']);
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
        return $formGroups;
    }

    /**
     * Get Form Libraries
     *
     * @return array
     */
    private function getFormLibraries():array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            return $this->clientService->getFormLibraries($this->args);
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Get available Forms
     *
     * @param $libraryID
     * @return array
     */
    private function getFormLibraryForms(string $libraryID):array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            return $this->clientService->getFormLibraryForms($libraryID, $this->args['account_id']);
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}
