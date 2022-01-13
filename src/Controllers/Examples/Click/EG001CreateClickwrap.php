<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\Examples\Click\CreateClickwrapService;

class EG001CreateClickwrap extends ClickApiBaseController
{
    const EG = 'ceg001'; # reference (and URL) for this example
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
     * 3. Return created clickwrap data
     *
     * @return void
     */
    function createController(): void
    {
        $this->checkDsToken();
        $clickwrapSummaryResponse = CreateClickwrapService::createClickwrap(
            $this->args, 
            self::DEMO_DOCS_PATH, 
            $this->clientService);

        if ($clickwrapSummaryResponse) {
            $clickwrap_name = $clickwrapSummaryResponse['clickwrapName'];
            $clickwrapSummaryResponse = json_decode((string)$clickwrapSummaryResponse, true);
            $this->clientService->showDoneTemplate(
                "Creating a clickwrap example",
                "Creating a clickwrap example",
                "Clickwrap $clickwrap_name has been created",
                json_encode(json_encode($clickwrapSummaryResponse))
            );
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_name' => $this->checkInputValues($_POST['clickwrap_name'])
        ];
    }
}
