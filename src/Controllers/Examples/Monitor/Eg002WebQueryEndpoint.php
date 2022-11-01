<?php

namespace Example\Controllers\Examples\Monitor;

use Example\Controllers\MonitorBaseController;
use Example\Services\Examples\Monitor\WebQueryEndpointService;
use Example\Services\JWTService;

class Eg002WebQueryEndpoint extends MonitorBaseController
{
    const EG = 'meg002'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * Check the access token and call the worker method
     * @return void
     */
    public function createController(): void
    {
        $accessToken = $_SESSION['ds_access_token'];
        $tokenExpirationTime = $_SESSION['ds_expiration'];
        if (
            is_null($accessToken) ||
            (time() + JWTService::TOKEN_REPLACEMENT_IN_SECONDS) > $tokenExpirationTime
        ) {
            $auth = new JWTService();
            $auth->login();
        } else {
            $monitoringData = WebQueryEndpointService::postWebQueryMethod(
                $this->clientService,
                $this->args['account_id'],
                $this->args['start_date'],
                $this->args['end_date']
            );

            if ($monitoringData) {
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode($monitoringData)
                );
            }
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'start_date' => $this->checkInputValues($_POST['start_date']),
            'end_date' => $this->checkInputValues($_POST['end_date'])
        ];
    }
}
