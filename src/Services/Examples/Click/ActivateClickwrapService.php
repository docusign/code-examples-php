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
        # Step 3 Start
        $clickwrap_request = new ClickwrapRequest(['status' => 'active']);
        # Step 3 End

        try {
            # Step 4 Start
            $accounts_api = $clientService->accountsApi();
            $response = $accounts_api -> updateClickwrapVersion(
                $args['account_id'],
                $args['clickwrap_id'],
                $args['version_number'],
                $clickwrap_request
            );
            # Step 4 End
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }


        return $response;
    }

    public static function getInactiveClickwraps(
        RouterService $routerService,
        ClickApiClientService $clientService,
        array $args,
        string $eg
    ): array {
        $minimum_buffer_min = 3;
        if ($routerService->ds_token_ok($minimum_buffer_min)) {
            try {
                $apiClient = $clientService->accountsApi();
                $options = new GetClickwrapsOptions();
                $options -> setStatus('inactive');
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
