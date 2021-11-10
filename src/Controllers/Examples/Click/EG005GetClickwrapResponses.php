<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\Examples\Click\GetClickwrapResponseService;

class EG005GetClickwrapResponses extends ClickApiBaseController
{
    const EG = 'ceg005'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * 1. Get available clickwraps
     * 2. Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        # Get available clickwraps
        $clickwraps = GetClickwrapResponseService::getClickwraps(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );
        parent::controller(['clickwraps' => $clickwraps]);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Display clickwrap responses data
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        $clickwrapResponse = GetClickwrapResponseService::getClickwrapResponse($this->args, $this->clientService);

        if ($clickwrapResponse) {
            $clickwrapResponse = json_decode((string)$clickwrapResponse, true);
            array_walk_recursive(
                $clickwrapResponse,
                function (&$v) {
                    if (gettype($v) == 'string' && strlen($v) > 500) {
                        $v = 'String (Length = ' . strlen($v) . ')..';
                    }
                }
            );
            $this->clientService->showDoneTemplate(
                "Get clickwrap responses",
                "Get clickwrap responses",
                "Results from the ClickWraps::getClickwrap method:",
                json_encode(json_encode($clickwrapResponse))
            );
        }
    }

    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_id' => $this->checkInputValues($_POST['clickwrap_id']),
        ];
    }
}
