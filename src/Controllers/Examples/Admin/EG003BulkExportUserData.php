<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\BulkExportUserDataService;

class EG003BulkExportUserData extends AdminApiBaseController
{
    const EG = 'aeg003'; # reference (and url) for this example

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
     * @throws ApiException
     */
    public function createController(): void
    {
        $this->checkDsToken();

        try {
            $organizationId = $this->clientService->getOrgAdminId();

            $bulkExports = BulkExportUserDataService::getExportsData(
                $this->clientService,
                $this->args,
                $organizationId
            );
            $filePath = realpath(
                    $_SERVER["DOCUMENT_ROOT"]
                ) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "demo_documents" . DIRECTORY_SEPARATOR . "ExportedUserData.csv";
            if ($bulkExports) {
                $this->clientService->showDoneTemplate(
                    "Bulk export user data",
                    "Bulk export user data",
                    "User data exported to $filePath<br>from UserExport:getUserListExport method:",
                    json_encode(json_encode($bulkExports))
                );
            }
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
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
