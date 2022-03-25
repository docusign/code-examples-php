<?php

namespace Example\Services\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapAgreementsResponse;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;

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
            # Step 3 Start
            $accounts_api = $clientService->accountsApi();
            $response = $accounts_api->getClickwrapAgreements($args['account_id'], $args['clickwrap_id']);
            # Step 3 End
        } catch (ApiException $e) {
            error_log($e);
            $clientService->showErrorTemplate($e);
            exit;
        }

        return $response;
    }

    public static function getClickwraps(
        RouterService $routerService,
        ClickApiClientService $clientService,
        array $args,
        string $eg
    ): array {
        $minimum_buffer_min = 3;
        if ($routerService->ds_token_ok($minimum_buffer_min)) {
            try {
                $apiClient = $clientService->accountsApi();
                return $apiClient->getClickwraps($args['account_id'])['clickwraps'];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
