<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\PermissionChangeSingleSettingService;
use Example\Services\ManifestService;

class EG026PermissionChangeSingleSetting extends eSignBaseController
{
    const EG = 'eg026'; # reference (and URL) for this example
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
        $permissionProfile = PermissionChangeSingleSettingService::permissionChangeSingleSetting(
            $this->args,
            $this->clientService
        );

        if ($permissionProfile) {
            # That need an envelope_id
            $permissionProfile = json_decode((string)$permissionProfile, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($permissionProfile)),
                ManifestService::replacePlaceholders(
                    "{0}",
                    $permissionProfile["permissionProfileId"],
                    $this->codeExampleText["ResultsPageText"]
                )
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
            'settings' => $this->getSettings(),
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'permission_args' => $permissions_args
        ];
    }
}
