<?php

namespace Example\Controllers\Examples\Click;

use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Model\ClickwrapVersionResponse;
use Example\Controllers\ClickApiBaseController;
use Example\Services\ClickApiClientService;
use Example\Services\RouterService;

class EG005GetClickwrapResponses extends ClickApiBaseController
{
    private ClickApiClientService $clientService;
    private RouterService $routerService;
    private array $args;
    private string $eg = "ceg005";  # reference (and URL) for this example

    /**
     * 1. Get available clickwraps
     * 2. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new ClickApiClientService($this->args);
        $this->routerService = new RouterService();

        # Get available clickwraps
        $clickwraps = $this->getClickwraps();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), ['clickwraps' => $clickwraps]);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Display clickwrap responses data
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
                array_walk_recursive($results, function (&$v) {
                    if (gettype($v) == 'string' && strlen($v) > 500) {
                        $v = 'String (Length = ' . strlen($v) . ')..';
                    }
                });
                $this->clientService->showDoneTemplate(
                    "Get clickwrap responses",
                    "Get clickwrap responses",
                    "Results from the ClickWraps::getClickwrap method:",
                    json_encode(json_encode($results))
                );
            }

        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * @param  $args array
     * @return ClickwrapVersionResponse
     */
    public function worker(array $args)
    {

        try {
            # Step 3 Start
            $accounts_api = $this->clientService->accountsApi();
            $response = $accounts_api->getClickwrap($args['account_id'], $args['clickwrap_id']);
            # Step 3 End
        } catch (ApiException $e) {
            error_log($e);
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

    private function getClickwraps(): array
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            try {
                $apiClient = $this->clientService->accountsApi();
                return $apiClient->getClickwraps($this->args['account_id'])['clickwraps'];
            } catch (ApiException $e) {
                error_log($e);
                return [];
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }
}
