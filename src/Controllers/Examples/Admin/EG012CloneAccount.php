<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\CloneAccountService;

class EG012CloneAccount extends AdminApiBaseController
{
    public const EG = 'aeg012'; # reference (and url) for this example
    public const FILE = __FILE__;

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->checkDsToken();

        try {
            $this->orgId = $this->clientService->getOrgAdminId();
            $provisionAssetGroupApi = $this->clientService->provisionAssetGroupApi();

            $assetGroupAccountsResponse = CloneAccountService::getAccounts($provisionAssetGroupApi, $this->orgId);
            parent::controller(['groups' => $assetGroupAccountsResponse['asset_group_accounts']]);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
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

        try {
            $this->orgId = $this->clientService->getOrgAdminId();
            $provisionAssetGroupApi = $this->clientService->provisionAssetGroupApi();

            $assetGroupAccountClone = CloneAccountService::cloneAccount(
                $provisionAssetGroupApi,
                $this->orgId,
                $this->args['source_account_id'],
                $this->args['target_account_name'],
                $this->args['target_account_first_name'],
                $this->args['target_account_last_name'],
                $this->args['target_account_email']
            );

            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode($assetGroupAccountClone->__toString())
            );
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
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
            'source_account_id' =>  $this->checkInputValues($_POST['source_account_id']),
            'target_account_name' =>  $this->checkInputValues($_POST['target_account_name']),
            'target_account_first_name' =>  $this->checkInputValues($_POST['target_account_first_name']),
            'target_account_last_name' =>  $this->checkInputValues($_POST['target_account_last_name']),
            'target_account_email' =>  $this->checkInputValues($_POST['target_account_email']),
        ];
    }
}
