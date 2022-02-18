<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\AssignFormToFormGroupService;

class Eg009AssignFormToFormGroup extends RoomsApiBaseController
{
    const EG = 'reg009'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * 1. Get available forms
     * 2. Get available form groups
     * 3. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        # Step 3 Start
        $forms = AssignFormToFormGroupService::getForms(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        # Step 3 End

        # Step 4 Start
        $formGroups = AssignFormToFormGroupService::getFormGroups(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        parent::controller(null, null, $forms, null, $formGroups);
        # Step 4 End
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Render request results
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        AssignFormToFormGroupService::assignFormToFormGroup($this->args, $this->clientService);
        $group_id = $this->args['form_group_id'];
        $form_id = $this->args['form_id'];
        $this->clientService->showDoneTemplate(
            "Assign a form to a form group",
            "Assign a form to a form group",
            "Form $form_id has been assigned to Form Group ID $group_id"
        );
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'form_id' => $this->checkInputValues($_POST['form_id']),
            'form_group_id' => $this->checkInputValues($_POST['form_group_id'])
        ];
    }
}
