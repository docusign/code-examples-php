<?php

namespace Example\Controllers\Examples\Monitor;

use Example\Controllers\MonitorBaseController;
use Example\Services\Examples\Monitor\GetMonitoringDataService;
use Example\Services\JWTService;

class Eg001GetMonitoringData extends MonitorBaseController
{
    const EG = 'meg001'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
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
        if (is_null($accessToken) ||
            (time() + JWTService::TOKEN_REPLACEMENT_IN_SECONDS) > $tokenExpirationTime
        ) {
            $auth = new JWTService();
            $auth->login();
        } else {
            // Call the worker method
            $monitoringData = GetMonitoringDataService::getMonitoringData($this->clientService);

            if ($monitoringData) {
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode(json_encode($monitoringData))
                );
            }
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $default_args = $this->getDefaultTemplateArgs();

        return $default_args;
    }
}
