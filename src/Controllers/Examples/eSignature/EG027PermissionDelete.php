<?php

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\PermissionDeleteService;

class EG027PermissionDelete extends eSignBaseController
{
    const EG = 'eg027'; # reference (and URL) for this example
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
        parent::controller(
            null,
            null,
            null,
            $permission_profiles
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
        $deleteResponse = PermissionDeleteService::permissionDelete($this->args, $this->clientService);

        if ($deleteResponse) {
            # That need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                $deleteResponse
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
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'permission_args' => $permissions_args
        ];
    }
}
