<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Api\BulkImportsApi;
use DocuSign\Admin\Client\ApiClient;
use DocuSign\Admin\Configuration;
use DocuSign\Admin\Model\OrganizationImportResponse;
use Example\Controllers\AdminBaseController;
use InvalidArgumentException;
use SplFileObject;

class EG004BulkImportUserData extends AdminBaseController
{

    const EG = 'aeg004'; # reference (and url) for this example

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
        $results = $this->bulkImportUserData();

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Bulk import user data",
                "Admin API data response output:",
                "Results from the bulk user import:",
                json_encode(json_encode($results))
            );
        }
    }

    /**
     * Method to prepare headers and create a bulk-import.
     * @throws ApiException for API problems.
     * @throws \DocuSign\Admin\Client\ApiException
     */
    private function bulkImportUserData()
    {
        $config = new Configuration();
        $accessToken = $_SESSION['ds_access_token'];

        $config->setAccessToken($accessToken);
        $config->setHost('https://api-d.docusign.net/management');
        $config->addDefaultHeader("Content-Disposition", "attachment; filename=file.csv");
        $apiClient = new ApiClient($config);

        $bulkImport = new BulkImportsApi($apiClient);
        $csvFile = dirname(__DIR__, 4) . "\public\demo_documents\bulkimport.csv";
        $str = file_get_contents($csvFile);
        $str = str_replace("<accountId>", $GLOBALS['DS_CONFIG']['account_id'], $str);
        file_put_contents($csvFile, $str);

        $result = $bulkImport->createBulkImportAddUsersRequest(
            $this->clientService->getOrgAdminId($this->args),
            new SplFileObject($csvFile)
        );

        $str = str_replace($GLOBALS['DS_CONFIG']['account_id'], "<accountId>", $str);
        file_put_contents($csvFile, $str);

        $_SESSION['import_id'] = strval($result->getId());

        return json_decode($result->__toString());
    }
    
    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];
    }
}
