<?php

namespace Example\Services\Examples\Monitor;

use DocuSign\Monitor\Api\DataSetApi;
use DocuSign\Monitor\Api\DataSetApi\GetStreamOptions;
use DocuSign\Monitor\Client\ApiException;
use Example\Services\MonitorApiClientService;

class GetMonitoringDataService
{
    /**
     * Method to get and display all of your organization’s monitoring data.
     * @param MonitorApiClientService $clientService
     * @return array
     */
    public static function getMonitoringData(MonitorApiClientService $clientService): array
    {
        // Create an ApiClient and construct API headers
        $apiClient = $clientService->getApiClient();

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
            $clientService->showErrorTemplate($e);
            exit;
        }
        # step 3 end

        // Cleaning the data from wrong symbols
        $results = json_encode($results);
        $results = str_replace("'", "", $results);
        return json_decode($results, true);
    }
}
