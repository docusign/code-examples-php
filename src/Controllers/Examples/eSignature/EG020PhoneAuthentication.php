<?php

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\PhoneAuthenticationService;
use DocuSign\Services\ManifestService;

class EG020PhoneAuthentication extends eSignBaseController
{
    const EG = 'eg020'; # reference (and URL) for this example
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
     *
     * @return void
     */
    public function createController(): void
    {
        # Step 1: Obtain your OAuth Token
        $this->checkDsToken();

        try {
            $envelopeAuthentification = PhoneAuthenticationService::phoneAuthentication(
                $this->args,
                $this::DEMO_DOCS_PATH,
                $this->clientService,
                $this->codeExampleText["CustomErrorTexts"][0]["ErrorMessage"]
            );
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
        if ($envelopeAuthentification) {
            $_SESSION["envelope_id"] = $envelopeAuthentification["envelope_id"]; # Save for use by other examples
            # which need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders(
                    "{0}",
                    $envelopeAuthentification["envelope_id"],
                    $this->codeExampleText["ResultsPageText"]
                )
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
            'signer_country_code' => $this->checkEmailInputValue($_POST['country_code']),
            'signer_phone_number' => $this->checkEmailInputValue($_POST['phone_number']),
        ];

        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
