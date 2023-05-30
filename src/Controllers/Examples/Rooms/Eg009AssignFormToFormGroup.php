<?php

namespace Example\Controllers\Examples\Rooms;

use Example\Controllers\RoomsApiBaseController;
use Example\Services\Examples\Rooms\AssignFormToFormGroupService;
use Example\Services\ManifestService;

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

        #ds-snippet-start:Rooms9Step3
        $forms = AssignFormToFormGroupService::getForms(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        #ds-snippet-end:Rooms9Step3

        #ds-snippet-start:Rooms9Step4
        $formGroups = AssignFormToFormGroupService::getFormGroups(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        parent::controller(null, null, $forms, null, $formGroups);
        #ds-snippet-end:Rooms9Step4
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
        $this->clientService->showDoneTemplateFromManifest(
            $this->codeExampleText,
            null,
            ManifestService::replacePlaceholders(
                "{0}",
                $form_id,
                ManifestService::replacePlaceholders("{1}", $this->args["form_group_id"], $this->codeExampleText["ResultsPageText"])
            ),
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
