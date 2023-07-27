<?php

namespace Example\Services\Examples\Admin;


use DocuSign\Admin\Client\ApiException as ApiExceptionAlias;
use Example\Services\AdminApiClientService;
use Exception;
use SplFileObject;

class BulkImportUserDataService
{
    /**
     * Method to prepare headers and create a bulk-import.
     * @param AdminApiClientService $clientService
     * @return mixed
     * @throws ApiExceptionAlias
     */
    public static function bulkImportUserData(AdminApiClientService $clientService, string $organizationId, string $emailAddress)
    {

        // $accountId = $clientService->getAccountId($emailAddress);
        $accountId = $_SESSION["ds_account_id"];
        $csvFile = dirname(__DIR__, 4) . "/public/demo_documents/bulkimport.csv";
        $str = file_get_contents($csvFile);
        $str = str_replace("<accountId>", $accountId , $str);



        file_put_contents($csvFile, $str);

        #ds-snippet-start:Admin4Step3
        $bulkImport = $clientService->bulkImportsApi();
        $organizationImportResponse = $bulkImport->createBulkImportAddUsersRequest(
            $organizationId,
            new SplFileObject($csvFile)
        );
        #ds-snippet-end:Admin4Step3

        $str = str_replace($accountId, "<accountId>", $str);
        file_put_contents($csvFile, $str);

        $_SESSION['import_id'] = strval($organizationImportResponse->getId());

        return json_decode($organizationImportResponse->__toString());
    }
}
