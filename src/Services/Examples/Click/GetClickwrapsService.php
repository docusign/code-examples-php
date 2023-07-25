<?php

namespace Example\Services\Examples\Click;

use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionsResponse;
use Example\Services\ClickApiClientService;

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
            $clickwrapVersionsResponse = $accountsApi->getClickwraps($args['account_id'], $options);
            #ds-snippet-end:Click4Step3
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $clickwrapVersionsResponse;
    }
}
