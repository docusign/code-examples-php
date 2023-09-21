<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\Examples\Click\GetClickwrapsService;

class EG004GetClickwraps extends ClickApiBaseController
{
    const EG = 'ceg004'; # reference (and URL) for this example
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
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return clickwraps data
     *
     * @return void
     */
    protected function createController(): void
    {
        $this->checkDsToken();
        $clickwrapResponse = GetClickwrapsService::getClickwraps($this->args, $this->clientService);

        if ($clickwrapResponse) {
            $clickwrapResponse = json_decode((string)$clickwrapResponse, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($clickwrapResponse))
            );
        }
    }

    public function getTemplateArgs(): array
    {
        $default = $this->getDefaultTemplateArgs();

        return $default;
    }
}
