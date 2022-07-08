<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\RetrieveDocuSignProfileByEmailAddress;

class EG006RetrieveDocuSignProfileByEmailAddress extends AdminApiBaseController
{
    const EG = 'aeg006'; # reference (and url) for this example

    const FILE = __FILE__;

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

            $usersResponse = RetrieveDocuSignProfileByEmailAddress::getDocuSignProfileByEmailAddress(
                $organizationId,
                $this->args["email"],
                $this->clientService);

            $this->clientService->showDoneTemplate(
                "Retrieve the user's DocuSign profile using an email address",
                "Retrieve the user's DocuSign profile using an email address",
                "Results from MultiProductUserManagement:getUserDSProfilesByEmail method:",
                json_encode(json_encode($usersResponse))
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
            'email' => $this->checkInputValues($_POST['email']),
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
