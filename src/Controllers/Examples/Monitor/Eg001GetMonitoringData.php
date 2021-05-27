<?php

namespace Example\Controllers\Examples\Monitor;

use Example\Controllers\MonitorBaseController;
use Example\Services\MonitorApiClientService;
use Example\Services\RouterService;
use Example\Services\JWTService;
use DocuSign\Monitor\Api\DataSetApi;
use DocuSign\Monitor\Model\CursoredResult;
use DocuSign\Monitor\Api\DataSetApi\GetStreamOptions;
use DocuSign\Monitor\Client\ApiException;

use function GuzzleHttp\json_decode;

class Eg001GetMonitoringData extends MonitorBaseController
{
    /** Monitor client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "meg001";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new MonitorApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems and perhaps file access\Exception too.
     */
    public function createController(): void
    {
        $accessToken =  $_SESSION['ds_access_token'];
        $tokenExpirationTime = $_SESSION['ds_expiration'];
        if (is_null($accessToken) ||
            (time() +  JWTService::TOKEN_REPLACEMENT_IN_SECONDS) > $tokenExpirationTime) {
            $auth = new JWTService();
            $auth->login();
        } else {
            // Call the worker method
            $results = $this->getMonitoringData();

            // Cleaning the data from wrong symbols
            $results = json_encode($results);
            $results = str_replace("'", "", $results);
            $results = json_decode($results, true);

            if ($results) {
                $this->clientService->showDoneTemplate(
                    "Monitoring data",
                    "Monitoring data result",
                    "Results from DataSet:GetStream method:",
                    json_encode(json_encode($results))
                );
            } else {
                echo "<script>alert('no dice')</script";
            }
        }
    }

    /**
     * Method to get and display all of your organization’s monitoring data.
     * @return array
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    private function getMonitoringData(): array
    {
        // Create an ApiClient and construct API headers
        $apiClient = $this->clientService->getApiClient();

        # step 3 start
        try {
            // Get monitoring data
            $datasetApi = new DataSetApi($apiClient);

            $cursor = "";
            $complete = false;
            $options = new GetStreamOptions();
            $results = array();

            // First call the endpoint with no cursor to get the first records.
            // After each call, save the cursor and use it to make the next 
            // call from the point where the previous one left off when iterating through
            // the monitoring records
            do {
                $options->setCursor($cursor);
                $result = $datasetApi->getStream('monitor', '2.0', $options);

                $key = "end_cursor";
                $endCursor = $result->$key;

                // If the endCursor from the response is the same as the one that you already have,
		        // it means that you have reached the 
		        // end of the records
                if ($endCursor === $cursor) {
                    $complete = true;
                } else {
                    $cursor = $endCursor;
                    array_push($results, json_decode($result));
                }
            } while (!$complete);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        # step 3 end

        return $results;
    }

    /**
     * Get specific template arguments
     *
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
