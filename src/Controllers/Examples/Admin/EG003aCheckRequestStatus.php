<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\CheckRequestStatusService;

class EG003ACheckRequestStatus extends AdminApiBaseController
{
    const EG = 'aeg003a'; # reference (and url) for this example

    const FILE = __FILE__;

    private string $orgId;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();

        $this->orgId = $this->clientService->getOrgAdminId($this->args);
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     * @throws ApiException
     */
    public function createController(): void
    {
        $this->checkDsToken();

        $exportId = $_SESSION['export_id'];

        // Call the worker method
        $bulkExports = CheckRequestStatusService::checkRequestStatus($this->clientService, $this->orgId, $exportId);

        if ($bulkExports) {
            $this->clientService->showDoneTemplate(
                "Check request status",
                "Admin API data response output:",
                "Results from UserExport:getUserListExport method:",
                json_encode(json_encode($bulkExports))
            );
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return $this->getDefaultTemplateArgs();
    }
}
