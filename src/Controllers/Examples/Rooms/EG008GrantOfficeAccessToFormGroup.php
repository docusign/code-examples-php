<?php

namespace Example\Controllers\Examples\Rooms;

use DocuSign\Rooms\Client\ApiException;
use Example\Controllers\RoomsApiBaseController;
use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;

class EG008GrantOfficeAccessToFormGroup extends RoomsApiBaseController
{
    private RoomsApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "reg008";  # reference (and url) for this example

    /**
     * 1. Get available offices.
     * 2. Get available form groups.
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
        $offices = $this->getOffices();
        # Step 3 End

        # Step 4 Start
        $formGroups = $this->getFormGroups();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), null, null, null, $offices, $formGroups);
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
                "Grant office access to a form group",
                "Grant office access to a form group",
                "Results from the FormGroups::GrantOfficeAccessToFormGroup method" .
                "<pre>Code: 204 <br /> Description: Office was successfully assigned to the form group</pre>"
            );
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     *  Grant office access to a form group using SDK.
     *
     * @param  $args array
     * @return void
     */
    public function worker(array $args): void
    {

        try {

            # Step 5 Start
            $form_api = $this->clientService->getFromGroupsApi();
            $form_api->grantOfficeAccessToFormGroup($args['form_group_id'], $args['office_id'], $args["account_id"]);
            # Step 5 End
        
        } catch (ApiException $e) {
            error_log($e);
            $this->clientService->showErrorTemplate($e);
            exit;
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $office_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['office_id']);
        $form_group_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['form_group_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'office_id' => $office_id,
            'form_group_id' => $form_group_id
        ];
    }

    /**
     * Get available offices
     *
     * @return array
     */
    private function getOffices(): array
    {
        $minimum_buffer_min = 3;
        $offices = [];
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $offices = $this->clientService->getOffices($this->args['account_id']);
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
        return $offices;
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
}
