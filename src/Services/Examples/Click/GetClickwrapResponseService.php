<?php

namespace DocuSign\Services\Examples\Click;

use DateTime;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapAgreementsResponse;
use DocuSign\Services\ClickApiClientService;
use DocuSign\Services\RouterService;

class GetClickwrapResponseService
{
    /**
     * @param  $args array
     * @param ClickApiClientService $clientService
     * @return ClickwrapAgreementsResponse
     */
    public static function getClickwrapResponse(array $args, ClickApiClientService $clientService): ClickwrapAgreementsResponse
    {

        try {
            #ds-snippet-start:Click5Step3
            $accounts_api = $clientService->accountsApi();
            $response = $accounts_api->getClickwrapAgreementsWithHttpInfo($args['account_id'], $args['clickwrap_id']);

            $remaining = $response[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $response[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
            #ds-snippet-end:Click5Step3
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $response[0];
    }

    public static function getClickwraps(
        RouterService $routerService,
        ClickApiClientService $clientService,
        array $args,
        string $eg
    ): array {
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
            try {
                $apiClient = $clientService->accountsApi();
                $response = $apiClient->getClickwrapsWithHttpInfo($args['account_id']);

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
