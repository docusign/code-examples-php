<?php

namespace Example\Services\Examples\Click;

use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;

class ActivateClickwrapService
{
    /**
     * @param  $args array
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionSummaryResponse
     */
    public static function activateClickwrap(array $args, ClickApiClientService $clientService): ClickwrapVersionSummaryResponse
    {
        //ds-snippet-start:Click2Step3
        $clickwrap_request = new ClickwrapRequest(['status' => 'active']);
        //ds-snippet-end:Click2Step3

        try {
            //ds-snippet-start:Click2Step4
            $accounts_api = $clientService->accountsApi();
            $response = $accounts_api -> updateClickwrapVersion(
                $args['account_id'],
                $args['clickwrap_id'],
                $args['version_number'],
                $clickwrap_request
            );
            //ds-snippet-end:Click2Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }


        return $response;
    }

    public static function getClickwrapsByStatus(
        RouterService $routerService,
        ClickApiClientService $clientService,
        array $args,
        string $eg,
        string $status
    ): array {
        $minimum_buffer_min = 3;
        if ($routerService->ds_token_ok($minimum_buffer_min)) {
            try {
                $apiClient = $clientService->accountsApi();
                $options = new GetClickwrapsOptions();
                $options -> setStatus($status);
                return $apiClient->getClickwraps($args['account_id'], $options)['clickwraps'];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
