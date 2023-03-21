<?php

/**
 * Example 037: SMS Delivery with remote signer and carbon copy.
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\SMSDeliveryService;
use Example\Services\ManifestService;

class EG037SMSDelivery extends eSignBaseController
{
    const EG = 'eg037'; # reference (and URL) for this example
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
        $envelopeId = SMSDeliveryService::smsDelivery(
            $this->args,
            $this->clientService,
            $this::DEMO_DOCS_PATH,
            $GLOBALS['DS_CONFIG']['doc_docx'],
            $GLOBALS['DS_CONFIG']['doc_pdf']);

        if ($envelopeId) {
            $_SESSION["envelope_id"] = $envelopeId["envelope_id"]; # Save for use by other examples
                                                                # which need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders("{0}", $envelopeId["envelope_id"], $this->codeExampleText["ResultsPageText"])
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
            'signer_name' => $this->checkInputValues($_POST['signerName']),
            'signer_country_code' => $this->checkInputValues($_POST['countryCode']),
            'signer_phone_number' => $this->checkInputValues($_POST['phoneNumber']),
            'cc_name' => $this->checkInputValues($_POST['ccName']),
            'cc_country_code' => $this->checkInputValues($_POST['ccCountryCode']),
            'cc_phone_number' => $this->checkInputValues($_POST['ccPhoneNumber']),
            'status' => 'sent'
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
