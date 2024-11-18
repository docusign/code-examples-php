<?php

namespace DocuSign\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Controllers\AdminApiBaseController;
use DocuSign\Services\Examples\Admin\CreateAccountService;

class EG013CreateAccount extends AdminApiBaseController
{
    public const EG = 'aeg013'; # reference (and url) for this example
    public const FILE = __FILE__;

    /**
     * Create a new controller instance
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
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        try {
            $orgId = $this->clientService->getOrgAdminId();
            $provisionAssetGroupApi = $this->clientService->provisionAssetGroupApi();

            $planItem = CreateAccountService::getFirstPlanItem(
                $provisionAssetGroupApi,
                $orgId
            );

            if (!is_null($planItem)) {
                $createdAccount = CreateAccountService::createAccountBySubscription(
                    $provisionAssetGroupApi,
                    $orgId,
                    $this->args['email'],
                    $this->args['firstName'],
                    $this->args['lastName'],
                    $planItem['subscription_id'],
                    $planItem['plan_id']
                );
    
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode($createdAccount->__toString())
                );
            }
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
            'email' =>  $this->checkInputValues($_POST['email']),
            'firstName' =>  $this->checkInputValues($_POST['firstName']),
            'lastName' =>  $this->checkInputValues($_POST['lastName']),
        ];
    }
}
