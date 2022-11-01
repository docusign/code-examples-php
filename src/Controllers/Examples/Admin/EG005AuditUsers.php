<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\AuditUsersService;

class EG005AuditUsers extends AdminApiBaseController
{
    const EG = 'aeg005'; # reference (and url) for this example

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

        # Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        try {
            $organizationId = $this->clientService->getOrgAdminId();
            $auditedUsers = AuditUsersService::auditUsers($this->clientService, $this->args, $organizationId);

            if ($auditedUsers) {
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode(json_encode($auditedUsers))
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
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
