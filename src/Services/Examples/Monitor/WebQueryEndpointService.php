<?php

namespace Example\Services\Examples\Monitor;

use DocuSign\Monitor\Api\DataSetApi;
use DocuSign\Monitor\Model\WebQuery;
use DocuSign\Monitor\Client\ApiException;
use Example\Services\MonitorApiClientService;

class WebQueryEndpointService
{
    /**
     * Method to get companies data from monitor
     * @param MonitorApiClientService $clientService
     * @param string $accountId
     * @param string $filterStartDate
     * @param string $filterEndDate
     * @return string
     */
    public static function postWebQueryMethod(
        MonitorApiClientService $clientService,
        string $accountId,
        string $filterStartDate,
        string $filterEndDate): string
    {
        // Create an ApiClient and construct API headers
        $apiClient = $clientService->getApiClient();

        try {
            // Step 4 start
            $datasetApi = new DataSetApi($apiClient);
            $webQueryResult = $datasetApi->postWebQuery(
                'monitor',
                '2.0',
                self::preparePostWebQuery($accountId, $filterStartDate, $filterEndDate)
            );
            // Step 4 end

        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        // Cleaning the data from unsupported symbols
        return str_replace("'", "", $webQueryResult);
    }
    // Step 3 start
    public static function preparePostWebQuery(string $accountId, string $filterStartDate, string $filterEndDate): WebQuery
    {
        $webQueryOptions = new WebQuery();

        $datesFilter = (object)[
            "FilterName" => "Time",
            "BeginTime" => $filterStartDate,
            "EndTime" => $filterEndDate
        ];

        $accountIdFilter = (object)[
            "FilterName" => "Has",
            "ColumnName" => "AccountId",
            "Value" => $accountId
        ];

        $webQueryOptions->setFilters([$datesFilter, $accountIdFilter]);

        $aggregation = (object)[
            "aggregationName" => "Raw",
            "limit" => "1",
            "orderby" => ["Timestamp, desc"]
        ];

        $webQueryOptions->setAggregations([$aggregation]);

        return $webQueryOptions;
    }
    // Step 3 end
}
