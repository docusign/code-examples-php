<?php

/**
 * Example 002: Remote signer, cc, envelope has three documents
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\SigningViaEmailService;
use Example\Services\ManifestService;

class EG002SigningViaEmail extends eSignBaseController
{
    const EG = 'eg002'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     * @return void
     */

    public function __construct()
    {
        parent::__construct();

        if (!$_SESSION["createDraft"]) {
            parent::controller();
        } else {
            unset($_SESSION["createDraft"]);
        }
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();
        # 2. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $envelopeResponse = SigningViaEmailService::signingViaEmail(
            $this->args,
            $this->clientService,
            $this::DEMO_DOCS_PATH
        );

        if ($envelopeResponse) {
            $_SESSION["envelope_id"] = $envelopeResponse["envelope_id"]; # Save for use by other examples
                                                                # which need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders(
                    "{0}",
                    $envelopeResponse["envelope_id"],
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
        $status = "sent";
        if ($_SESSION["createDraft"]) {
            $status = "created";
        }

        $envelope_args = [
            'signer_email' => $this->checkEmailInputValue($_POST['signer_email']),
            'signer_name' => $this->checkInputValues($_POST['signer_name']),
            'cc_email' => $this->checkEmailInputValue($_POST['cc_email']),
            'cc_name' => $this->checkInputValues($_POST['cc_name']),
            'status' => $status
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
