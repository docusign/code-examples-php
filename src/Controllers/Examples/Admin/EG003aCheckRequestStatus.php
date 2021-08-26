<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminApiBaseController;

class EG003ACheckRequestStatus extends AdminApiBaseController
{

    const EG = 'aeg003a'; # reference (and url) for this example

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
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $this->checkDsToken();

        // Call the worker method
        $results = $this->checkRequestStatus();

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Check request status",
                "Admin API data response output:",
                "Results from UserExport:getUserListExport method:",
                json_encode(json_encode($results))
            );
        }
    }

    /**
     * Method to get a request status for bulk-export.
     * @throws \DocuSign\OrgAdmin\Client\ApiException
     */
    private function checkRequestStatus()
    {
        $bulkExportsApi = $this->clientService->bulkExportsAPI();

        $exportId = $_SESSION['export_id'];
        
        # Step 4 start
        $result = $bulkExportsApi->getUserListExport($this->clientService->getOrgAdminId($this->args), $exportId);
        # Step 4 end
        
        return json_decode($result->__toString());
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
