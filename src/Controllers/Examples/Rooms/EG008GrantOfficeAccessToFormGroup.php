<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\GrantOfficeAccessToFormGroupService;

class EG008GrantOfficeAccessToFormGroup extends RoomsApiBaseController
{

    const EG = 'reg008'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * 1. Get available offices.
     * 2. Get available form groups.
     * 3. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        # Step 3 Start
        $offices = GrantOfficeAccessToFormGroupService::getOffices(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        # Step 3 End

        # Step 4 Start
        $formGroups = GrantOfficeAccessToFormGroupService::getFormGroups(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        parent::controller(null, null, null, $offices, $formGroups);
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
        GrantOfficeAccessToFormGroupService::grantOfficeAccessToFormGroup($this->args, $this->clientService);
        $this->clientService->showDoneTemplate(
            "Grant office access to a form group",
            "Grant office access to a form group",
            "Results from the FormGroups::GrantOfficeAccessToFormGroup method" .
            "<pre>Code: 204 <br /> Description: Office was successfully assigned to the form group</pre>"
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
            'office_id' => $this->checkInputValues($_POST['office_id']),
            'form_group_id' => $this->checkInputValues($_POST['form_group_id'])
        ];
    }
}
