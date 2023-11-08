<?php

namespace DocuSign\Services\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Services\ClickApiClientService;
use DocuSign\Click\model\UserAgreementRequest;
use DocuSign\Services\RouterService;

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
        #ds-snippet-start:Click6Step3
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
        #ds-snippet-end:Click6Step3

        try {
            #ds-snippet-start:Click6Step4
            $response =  $accountsApi->createHasAgreed($args['account_id'], $args['clickwrap_id'], $documentData);
            if ($response->getStatus() == "created") {
                return $response->getAgreementUrl();
            } else {
                return "Already Agreed";
            }
            #ds-snippet-end:Click6Step4
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
        if ($routerService->dsTokenOk($GLOBALS['DS_CONFIG']['minimum_buffer_min'])) {
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
