<?php

namespace DocuSign\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Controllers\AdminApiBaseController;
use DocuSign\Services\Examples\Admin\CreateNewUserService;
use DocuSign\Services\SignatureClientService;

class EG001CreateNewUser extends AdminApiBaseController
{
    const EG = 'aeg001'; # reference (and url) for this example

    const FILE = __FILE__;

    private string $orgId;

    /**
     * Create a new controller instance.
     * @return void
     * @throws ApiException
     */
    public function __construct()
    {
        parent::__construct();

        $this->checkDsToken();

        try {
            $this->orgId = $this->clientService->getOrgAdminId($this->args);

            $signatureClientService = new SignatureClientService($this->args);
            $permission_profiles = $signatureClientService->getPermissionsProfiles($this->args);
            $groupsObj = $signatureClientService->getGroups($this->args);
            $args = [
                'permission_profiles' => $permission_profiles,
                'groups' => $groupsObj
            ];
            parent::controller($args);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $this->checkDsToken();

        // Call the worker method
        $args = $this->getTemplateArgs();
        $newUserResponse = CreateNewUserService::addActiveUser(
            $this->orgId,
            $args["envelope_args"],
            $this->clientService
        );

        if ($newUserResponse) {
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(($newUserResponse->__toString()))
            );
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $envelope_args = [
            'Name' => $this->checkInputValues($_POST['Name']),
            'FirstName' => $this->checkInputValues($_POST['FirstName']),
            'LastName' => $this->checkInputValues($_POST['LastName']),
            'Email' => $this->checkInputValues($_POST['Email']),
            'permission_profile_id' => $this->checkInputValues($_POST["permission_profile_id"]),
            'group_id' => $this->checkInputValues($_POST["group_id"])
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
