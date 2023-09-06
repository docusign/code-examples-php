<?php

namespace Example\Controllers\Examples\Click;

use DocuSign\Click\Client\ApiException;
use Example\Controllers\ClickApiBaseController;
use Example\Services\Examples\Click\EmbedClickwrapService;

class EG006EmbedClickwrap extends ClickApiBaseController
{
    const EG = 'ceg006'; # reference (and URL) for this example
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
        $clickwraps = EmbedClickwrapService::getClickwraps(
            $this->routerService,
            $this->clientService,
            $this->args,
            $this::EG
        );

        parent::controller(['clickwraps' => $clickwraps[0], 'inactives'=>$clickwraps[1]]);
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
        $clickwrapResponse = EmbedClickwrapService::createAgreeementUrl($this->args, $this->clientService);
        $htmlString = "<p id='agreementStatus'>NOT AGREED</p><div id='ds-terms-of-service'></div><script src='https://demo.docusign.net/clickapi/sdk/latest/docusign-click.js'></script><script>docuSignClick.Clickwrap.render({agreementUrl: '" . $clickwrapResponse . "',onAgreed: function () {document.getElementById('agreementStatus').innerHTML = 'AGREED';}}, '#ds-terms-of-service');</script>";
        if ($clickwrapResponse) {

            if($clickwrapResponse === 'Already Agreed'){
                $e = new ApiException("The email address was already used to agree to this elastic template. Provide a different email address if you want to view the agreement and agree to it.",990);
                $this->clientService->showErrorTemplate($e);
                exit;
            }



            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                $this->codeExampleText["ResultsPageText"]."<p>Agreement URL received back from API call: <code>".$clickwrapResponse."</code></p>".$htmlString
            );
            
        }
    }

    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'clickwrap_id' => $this->checkInputValues($_POST['clickwrap_id']),
            'full_name' => $this->checkInputValues($_POST['fullName']),
            'email_address' => $this->checkInputValues(($_POST['emailAddress'])),
            'company' => $this->checkInputValues($_POST['company']),
            'job_title' => $this->checkInputValues($_POST['jobTitle']),
            'date' => $this->checkInputValues($_POST['date'])
        ];
    }
}
