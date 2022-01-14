<?php

namespace Example\Controllers\Examples\Click;

use Example\Controllers\ClickApiBaseController;
use Example\Services\Examples\Click\CreateNewClickwrapVersionService;

class EG003CreateClickwrapVersion extends ClickApiBaseController
{

    const EG = 'ceg003'; # reference (and URL) for this example
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
        $clickwraps = CreateNewClickwrapVersionService::getClickwraps(
            $this->routerService,
            $this->args,
            $this->clientService,
            $this::EG
        );
        parent::controller(['clickwraps' => $clickwraps]);
    }

    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_name' => $this->checkInputValues($_POST['clickwrap_name']),
            'clickwrap_id' => $this->checkInputValues($_POST['clickwrap_id']),
        ];
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return created clickwrap version
     */
    function createController(): void
    {
        $this->checkDsToken();
        $clickwrapSummaryResponse = CreateNewClickwrapVersionService::createNewClickwrapVersion(
            $this->args,
            $this::DEMO_DOCS_PATH,
            $this->clientService
        );

        if ($clickwrapSummaryResponse) {
            $clickwrapSummaryResponse = json_decode((string)$clickwrapSummaryResponse, true);
            $clickwrap_name = $clickwrapSummaryResponse['clickwrapName'];
            $clickwrap_version = $clickwrapSummaryResponse['versionNumber'];
            
            $this->clientService->showDoneTemplate(
                "Creating a new clickwrap version example",
                "Creating a new clickwrap version example",
                "Version $clickwrap_version of clickwrap $clickwrap_name has been created",
                json_encode(json_encode($clickwrapSummaryResponse))
            );
        }
    }
}
