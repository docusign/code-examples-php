<?php

/**
 * Example 022: Remote signer, cc, envelope has three documents
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\KbAuthenticationService;
use Example\Services\ManifestService;

class EG022KbAuthentication extends eSignBaseController
{
    const EG = 'eg022'; # reference (and URL) for this example
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
        # Obtain your OAuth Token
        $this->checkDsToken();

        $envelopeAuthentification = KbAuthenticationService::kbAuthentification(
            $this->args,
            $this->clientService,
            $this::DEMO_DOCS_PATH
        );

        if ($envelopeAuthentification) {
            $_SESSION["envelope_id"] = $envelopeAuthentification["envelope_id"]; # Save for use by other examples
            # which need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders("{0}", $envelopeAuthentification["envelope_id"], $this->codeExampleText["ResultsPageText"])
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
