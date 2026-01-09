<?php

namespace DocuSign\Services\Examples\Click;

use DateTime;
use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionsResponse;
use DocuSign\Services\ClickApiClientService;

class GetClickwrapsService
{
    /**
     * Get account clickwraps
     * @param  $args array
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionsResponse
     */
    public static function getClickwraps(array $args, ClickApiClientService $clientService): ClickwrapVersionsResponse
    {
        try {
            #ds-snippet-start:Click4Step3
            $accountsApi = $clientService->accountsApi();
            $options = new GetClickwrapsOptions();
            $clickwrapVersionsResponse = $accountsApi->getClickwrapsWithHttpInfo($args['account_id'], $options);

            $remaining = $clickwrapVersionsResponse[2]['X-RateLimit-Remaining'] ?? null;
            $reset = $clickwrapVersionsResponse[2]['X-RateLimit-Reset'] ?? null;

            if ($remaining !== null && $reset !== null) {
                $resetInstant = (new DateTime())->setTimestamp((int)$reset);
                error_log("API calls remaining: $remaining");
                error_log("Next Reset: " . $resetInstant->format(\DateTime::ATOM));
            }
            #ds-snippet-end:Click4Step3
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $clickwrapVersionsResponse[0];
    }
}
