<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminBaseController;
use Example\Services\RouterService;
use DocuSign\Monitor\Client\ApiException;
use DocuSign\OrgAdmin\Client\ApiClient;
use DocuSign\OrgAdmin\Configuration;
use Example\Services\AdminApiClientService;

use function GuzzleHttp\json_decode;

class Ex004BulkImportUserData extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg004";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;

        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
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
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Method to prepare headers and create a bulk-import.
     * @throws ApiException for API problems.
     */
    private function bulkImportUserData()
    {
        $config = new Configuration();
        $accessToken =  $_SESSION['ds_access_token'];

        $config->setAccessToken($accessToken);
        $config->setHost('https://api-d.docusign.net/management');     
        $config->addDefaultHeader("Content-Disposition", "attachment; filename=myfile.csv");
        $apiClient = new ApiClient($config);

        $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];

        $userData = "AccountID,UserName,UserEmail,PermissionSet\n" .
        $GLOBALS['DS_CONFIG']['account_id'] . ",FirstLast1,exampleuser1@example.com,DS Viewer";

        $result = $this->createBulkImport($organizationId, $userData, $apiClient);

        $_SESSION['import_id'] = strval($result->getId());

        return json_decode($result->__toString());
    }

    /**
     * Method to call a request method and transform responce into OrganizationImportResponse
     * @return \DocuSign\OrgAdmin\Model\OrganizationImportResponse
     * @throws ApiException for API problems.
     */
    public function createBulkImport($organization_id, $userData, $apiClient): \DocuSign\OrgAdmin\Model\OrganizationImportResponse
    {
        list($response) = $this->createRequestForBulkImport($organization_id, $userData, $apiClient);
        return $response;
    }

    /**
     * Method to create a POST request to the server.
     * @return array
     * @throws ApiException for API problems.
     */
    public function createRequestForBulkImport($organization_id, $_tempBody, $apiClient): array
    {
        if ($organization_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $organization_id when calling createBulkImportAddUsersRequest');
        }
        
        $resourcePath = "/v2/organizations/" . $organization_id . "/imports/bulk_users/add";
        $httpBody = $_tempBody ?? ''; 

        $queryParams = $headerParams = [];
        $headerParams['Accept'] ??= $apiClient->selectHeaderAccept(['application/json']);
        $headerParams['Content-Type'] = $apiClient->selectHeaderContentType(['text/csv']);
        
        if (strlen($apiClient->getConfig()->getAccessToken()) !== 0) {
            $headerParams['Authorization'] = 'Bearer ' . $apiClient->getConfig()->getAccessToken();
        }

        try {
            list($response, $statusCode, $httpHeader) = $apiClient->callApi(
                $resourcePath,
                'POST',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\OrgAdmin\Model\OrganizationImportResponse',
                '/v2/organizations/{organizationId}/imports/bulk_users/add'
            );

            return [$apiClient->getSerializer()->deserialize($response, '\DocuSign\OrgAdmin\Model\OrganizationImportResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\OrgAdmin\Model\OrganizationImportResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }
}
