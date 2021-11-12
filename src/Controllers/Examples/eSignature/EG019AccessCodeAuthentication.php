<?php

/**
 * Example 019: Access-code authentication for recipient
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\AccessCodeAuthenticationService;

class EG019AccessCodeAuthentication extends eSignBaseController
{
    const EG = 'eg019'; # reference (and URL) for this example
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
     * 3. Redirect the user to the embedded signing
     *
     * @return void
     */
    public function createController(): void
    {
        # Step 1: Obtain your OAuth Token
        $this->checkDsToken();

        $envelopeId = AccessCodeAuthenticationService::accessCodeAuthentication($this->args, $this->clientService, $this::DEMO_DOCS_PATH);

        if ($envelopeId) {
            $_SESSION["envelope_id"] = $envelopeId["envelope_id"]; # Save for use by other examples
                                                                # which need an envelope_id
            $this->clientService->showDoneTemplate(
                "Envelope sent",
                "Envelope sent",
                "The envelope has been created and sent!<br/>
                    Envelope ID {$envelopeId["envelope_id"]}."
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
        $envelope_args = [
            'signer_email' => $this->checkEmailInputValue($_POST['signer_email']),
            'signer_name' => $this->checkInputValues($_POST['signer_name']),
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
