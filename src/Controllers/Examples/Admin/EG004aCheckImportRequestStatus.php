<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\CheckImportRequestStatusService;

class EG004ACheckImportRequestStatus extends AdminApiBaseController
{

    const EG = 'aeg004a'; # reference (and url) for this example

    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws \DocuSign\OrgAdmin\Client\ApiException
     * @throws \DocuSign\Admin\Client\ApiException
     */
    public function createController(): void
    {
        $this->checkDsToken();

        $importId = $_SESSION['import_id'];
        $organizationId = $this->clientService->getOrgAdminId();
        // Call the worker method
        $results = CheckImportRequestStatusService::checkRequestStatus(
            $this->clientService,
            $organizationId,
            $importId
        );

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Check import request status",
                "Admin API data response output:",
                "Results from UserImport:getBulkUserImportRequest method:",
                json_encode($results)
            );
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $default_args = $this->getDefaultTemplateArgs();

        return $default_args;
    }
}
