<?php

namespace Example\Services\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use Example\Services\ClickApiClientService;
use DocuSign\Click\model\UserAgreementRequest;
use Example\Services\RouterService;

class EmbedClickwrapService
{
    /**
     * 1. Build the display settings
     * 2. Build array of documents
     * 3. Build clickwrap
     * 4. Submit clickwrap using SDK
     * @param  $args array
     * @param string $demoDocsPath
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionSummaryResponse
     */
    public static function createAgreeementUrl(
        array $args,
        ClickApiClientService $clientService
    ): string {
        # Step 3 Start
        $accountsApi = $clientService->accountsApi();
        $documentData = new UserAgreementRequest();
        $documentData->setClientUserId($args["email_address"]);
        $rawData  = [
            'fullName' => $args["full_name"],
            'email' => $args["email_address"],
            'company' => $args["company"],
            'title' => $args['job_title'],
            'date' => $args['date']
        ];
        $documentData->setDocumentData($rawData);

        try {
            $response =  $accountsApi->createHasAgreed($args['account_id'], $args['clickwrap_id'], $documentData);
            if ($response->getStatus() == "created") {
                return $response->getAgreementUrl();
            } else {
                return "Already Agreed";
            }

            # Step 3 End
        } catch (ApiException $e) {
            // the clickwrap id is not active, therefore we route them to example 2 to activate it
            $clientService->showErrorTemplate($e);
            exit;
        }
        return $response;
    }

    /**
     * 1. Retrieve Active Clickwraps
     * @param  $args array
     * @param string $demoDocsPath
     * @param ClickApiClientService $clientService
     * @return ClickwrapVersionSummaryResponse
     */
    public static function getClickwraps(
        RouterService $routerService,
        ClickApiClientService $clientService,
        array $args,
        string $eg
    ): array {
        $minimum_buffer_min = 3;
        if ($routerService->ds_token_ok($minimum_buffer_min)) {
            try {
                $anyClickwraps = [];
                $apiClient = $clientService->accountsApi();
                $options = new GetClickwrapsOptions();
                $options->setStatus('active');
                $activeClickwraps = $apiClient->getClickwraps($args['account_id'], $options)['clickwraps'];
                if (empty($activeClickwraps)) {
                    $options->setStatus('inactive');
                    $anyClickwraps = $apiClient->getClickwraps($args['account_id'], $options)['clickwraps'];
                }


                    return [ $activeClickwraps, $anyClickwraps];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $clientService->needToReAuth($eg);
        }
    }
}
