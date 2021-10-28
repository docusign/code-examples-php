<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\AuditUsersService;
use Example\Controllers\Examples\Admin\ApiException;

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
        $organizationId = $this->clientService->getOrgAdminId();
        $results = AuditUsersService::auditUsers($this->clientService, $this->args, $organizationId);

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Audit users",
                "Audit users",
                "Results from eSignUserManagement:getUserProfiles method:",
                json_encode(json_encode($results))
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
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
