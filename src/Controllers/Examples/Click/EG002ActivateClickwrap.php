<?php

namespace DocuSign\Controllers\Examples\Click;

use DocuSign\Controllers\ClickApiBaseController;
use DocuSign\Services\Examples\Click\ActivateClickwrapService;

class EG002ActivateClickwrap extends ClickApiBaseController
{
    const EG = 'ceg002'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * 1. Get available inactive clickwraps
     * 2. Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        # Step 1. Get available inactive clickwraps

        $inactiveClickwraps = ActivateClickwrapService::getClickwrapsByStatus(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG,
            "inactive"
        );

        $draftClickwraps = ActivateClickwrapService::getClickwrapsByStatus(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG,
            "draft"
        );
        parent::controller(['clickwraps' => array_merge($inactiveClickwraps, $draftClickwraps)]);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Display activated clickwrap data
     *
     * @return void
     */
    protected function createController(): void
    {
        $this->checkDsToken();
        $clickwrapSummaryResponse = ActivateClickwrapService::activateClickwrap($this->args, $this->clientService);

        if ($clickwrapSummaryResponse) {
            $clickwrap_name = $clickwrapSummaryResponse['clickwrapName'];
            $clickwrapSummaryResponse = json_decode((string)$clickwrapSummaryResponse, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($clickwrapSummaryResponse))
            );
        }
    }

    public function getTemplateArgs(): array
    {
        $clickwrap = $this->checkInputValues($_POST['clickwrap']);
        list($clickwrap_id, $version_number) = explode(",", $clickwrap);
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_id' => $clickwrap_id,
            'version_number' => $version_number
        ];
    }
}
