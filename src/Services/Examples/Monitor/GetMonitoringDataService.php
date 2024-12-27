<?php

namespace DocuSign\Services\Examples\Monitor;

use DocuSign\Monitor\Api\DataSetApi;
use DocuSign\Monitor\Api\DataSetApi\GetStreamOptions;
use DocuSign\Monitor\Client\ApiException;
use DocuSign\Services\MonitorApiClientService;

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

        #ds-snippet-start:Monitor1Step3
        try {
            // Get monitoring data
            $datasetApi = new DataSetApi($apiClient);

            $cursor = "";
            $complete = false;
            $options = new GetStreamOptions();
            $options->setLimit(2000);
            $monitoringLogs = array();

            // First call the endpoint with no cursor to get the first records.
            // After each call, save the cursor and use it to make the next
            // call from the point where the previous one left off when iterating through
            // the monitoring records
            do {
                $options->setCursor($cursor);
                $cursoredResult = $datasetApi->getStream('monitor', '2.0', $options);

                $key = "end_cursor";
                $endCursor = $cursoredResult->$key;

                // If the endCursor from the response is the same as the one that you already have,
                // it means that you have reached the
                // end of the records
                if ($endCursor === $cursor) {
                    $complete = true;
                } else {
                    $cursor = $endCursor;
                    array_push($monitoringLogs, json_decode($cursoredResult));
                }
            } while (!$complete);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        #ds-snippet-end:Monitor1Step3

        // Cleaning the data from wrong symbols
        $monitoringLogs = json_encode($monitoringLogs);
        $monitoringLogs = str_replace("'", "", $monitoringLogs);
        return json_decode($monitoringLogs, true);
    }
}
