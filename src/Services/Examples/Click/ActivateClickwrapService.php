<?php

namespace DocuSign\Services\Examples\Click;

use DateTime;
use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\RouterService;

class ActivateClickwrapService
{
    /**
     * @param  $args array
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionSummaryResponse
     */
    public static function activateClickwrap(array $args, ClickApiClientService $clientService): ClickwrapVersionSummaryResponse
    {
        #ds-snippet-start:Click2Step3
        $clickwrap_request = new ClickwrapRequest(['status' => 'active']);
        #ds-snippet-end:Click2Step3

        try {
            #ds-snippet-start:Click2Step4
            $accounts_api = $clientService->accountsApi();
            $response = $accounts_api->updateClickwrapVersionWithHttpInfo(
                $args['account_id'],
                $args['clickwrap_id'],
                $args['version_number'],
                $clickwrap_request
            );

            $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $response[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }

            #ds-snippet-end:Click2Step4
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $response[0];
    }

    public static function getClickwrapsByStatus(
        RouterService $routerService,
        ClickApiClientService $clientService,
        array $args,
        string $eg,
        string $status
    ): array {
        $minimum_buffer_min = 3;
        if ($routerService->dsTokenOk($minimum_buffer_min)) {
            try {
                $apiClient = $clientService->accountsApi();
                $options = new GetClickwrapsOptions();
                $options -> setStatus($status);
                $response = $apiClient->getClickwrapsWithHttpInfo($args['account_id'], $options);

                $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
                $reset = $response[2]['X-RateLimit-Reset'] ?? null;

                if ($remaining !== null && $reset !== null) {
                    $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                    error_log("API calls remaining: $remaining");
                    error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
                }

                return $response[0]['clickwraps'];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
