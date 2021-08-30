<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminApiBaseController;
use Example\Controllers\AdminBaseController;
use SplFileObject;

class EG004BulkImportUserData extends AdminApiBaseController
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
                "Add users via bulk import",
                "Add users via bulk import",
                "Results from UserImport:addBulkUserImport method:",
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

        
        
        $csvFile = dirname(__DIR__, 4) . "\public\demo_documents\bulkimport.csv";
        $str = file_get_contents($csvFile);
        $str = str_replace("<accountId>", $GLOBALS['DS_CONFIG']['account_id'], $str);
        file_put_contents($csvFile, $str);
        
        # Step 3 start
        $bulkImport = $this->clientService->bulkImportsApi();
        $result = $bulkImport->createBulkImportAddUsersRequest(
            $this->clientService->getOrgAdminId($this->args),
            new SplFileObject($csvFile)
        );
        # Step 3 end

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
        $default_args = $this->getDefaultTemplateArgs();

        return $default_args;
    }
}
