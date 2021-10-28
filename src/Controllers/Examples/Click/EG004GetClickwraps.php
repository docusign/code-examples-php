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
    function createController(): void
    {
        $this->checkDsToken();
        $results = GetClickwrapsService::getClickwraps($this->args, $this->clientService);

        if ($results) {
            $results = json_decode((string)$results, true);
            $this->clientService->showDoneTemplate(
                "Get a list of clickwraps",
                "Get a list of clickwraps",
                "Results from the ClickWraps::getClickwraps method:",
                json_encode(json_encode($results))
            );
        }
    }

    public function getTemplateArgs(): array
    {
        $default = $this->getDefaultTemplateArgs();

        return $default;
    }
}
