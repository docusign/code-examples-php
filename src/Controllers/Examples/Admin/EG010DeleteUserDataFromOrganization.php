<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\DeleteUserDataFromOrganizationService;

class EG010DeleteUserDataFromOrganization extends AdminApiBaseController
{
    public const EG = 'aeg010'; # reference (and url) for this example
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
            $organizationId = $this->clientService->getOrgAdminId();
            $usersApi = $this->clientService->getUsersApi();
            $organizationsApi = $this->clientService->getOrganizationsApi();

            $individualUserDataRedactionResponse = DeleteUserDataFromOrganizationService::deleteUserDataFromOrganization(
                $usersApi,
                $organizationsApi,
                $organizationId,
                $this->args['email']
            );

            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode($individualUserDataRedactionResponse->__toString())
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
            'email' =>  $this->checkInputValues($_POST['email']),
        ];
    }
}
