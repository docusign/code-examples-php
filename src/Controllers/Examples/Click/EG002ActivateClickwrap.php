<?php

namespace Example\Controllers\Examples\Click;

use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Model\ClickwrapVersionSummaryResponse;
use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;

class EG002ActivateClickwrap extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg002";  # reference (and URL) for this example
    
    /**
     * 1. Get available inactive clickwraps
     * 2. Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();

        # Step 1. Get available inactive clickwraps
        $inactiveClickwraps = $this->getInactiveClickwraps();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), ['clickwraps' => $inactiveClickwraps]);
    }
    
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Display activated clickwrap data
     *
     * @return void
     */
    function createController()
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->worker($this->args);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Activate Clickwrap",
                    "Activate Clickwrap",
                    "Clickwrap activated",
                    json_encode(json_encode($results))
                );
            }

        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * @param  $args array
     * @return ClickwrapVersionSummaryResponse
     */
    public function worker(array $args): ClickwrapVersionSummaryResponse
    {
        # Step 3 Start
        $clickwrap_request = new ClickwrapRequest(['status' => 'active']);
        # Step 3 End

        
        try{
            # Step 4 Start
            $accounts_api = $this->clientService->accountsApi();
            $response = $accounts_api -> updateClickwrapVersion(
                $args['account_id'], $args['clickwrap_id'], '1', $clickwrap_request
            );
            # Step 4 End
        }  catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        

        return $response;
    }

    private function getTemplateArgs(): array
    {
        $clickwrap_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['clickwrap_id']);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_id' => $clickwrap_id,
        ];
    }

    private function getInactiveClickwraps():array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            try{
                $apiClient = $this->clientService->accountsApi();
                $options = new GetClickwrapsOptions();
                $options -> setStatus('inactive');
                return $apiClient->getClickwraps($this->args['account_id'], $options)['clickwraps'];
            }  catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}
