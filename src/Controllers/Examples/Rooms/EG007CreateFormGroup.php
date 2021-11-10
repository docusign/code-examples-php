<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\CreateFormGroupService;

class EG007CreateFormGroup extends RoomsApiBaseController
{
    const EG = 'reg007'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return created FormGroup
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        $formGroup = CreateFormGroupService::createFormGroup($this->args, $this->clientService);
        if ($formGroup) {
            $formGroup = json_decode((string)$formGroup, true);
            $this->clientService->showDoneTemplate(
                "Create a form group",
                "Create a form group",
                "Results of FormGroups::createFormGroup",
                json_encode(json_encode($formGroup))
            );
        }
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
            'form_group_name' => $this->checkInputValues($_POST['form_group_name']),
        ];
    }
}
