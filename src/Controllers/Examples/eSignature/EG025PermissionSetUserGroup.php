<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\PermissionSetUserGroupService;

class EG025PermissionSetUserGroup extends eSignBaseController
{
    const EG = 'eg025'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $permission_profiles = $this->clientService->getPermissionsProfiles($this->args);
        $groups = $this->clientService->getGroups($this->args);
        parent::controller(
            null,
            null,
            null,
            $permission_profiles,
            $groups
        );
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        # 1. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $results = json_decode(PermissionSetUserGroupService::permissionSetUserGroup($this->args, $this->clientService), true);

        if ($results) {
            # That need an envelope_id
            $this->clientService->showDoneTemplate(
                "Set a permission profile to a group of users",
                "Set a permission profile to a group of users",
                "The permission profile has been set!<br/>
                Permission profile id: {$results['groups'][0]['permissionProfileId']}<br/>
                Group id: {$results['groups'][0]['groupId']}"
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
        $permissions_args = [
            'permission_profile_id' => $this->checkInputValues($_POST['permission_profile_id']),
            'group_id' => $this->checkInputValues($_POST['group_id']),
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'permission_args' => $permissions_args
        ];
    }
}
