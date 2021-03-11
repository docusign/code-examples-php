<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;
use DocuSign\Click\Api\AccountsApi\GetClickwrapsOptions;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionsResponse;

class EG004GetClickwraps extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg004"; # reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }
    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return clickwraps data
     *
     * @return void
     */
    function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            $results = $this->worker($this->args);

            if ($results) {
                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Get a list of clickwraps",
                    "Get a list of clickwraps",
                    "Results from the ClickWraps::getClickwraps method:",
                    json_encode(json_encode($results))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }

    }

    /**
     * Get account clickwraps
     *
     * @param  $args array
     * @return ClickwrapVersionsResponse
     */
    public function worker(array $args): ClickwrapVersionsResponse
    {
        
        try {
            # Step 3 Start
            $accountsApi = $this->clientService->accountsApi();
            $options = new GetClickwrapsOptions();
            $results = $accountsApi->getClickwraps($args['account_id'], $options);
            # Step 3 End
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }
        return $results;
    }

    private function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
