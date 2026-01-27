<?php
/**
 * Example 046: Send an envelope with a remote signer and cc recipient o be notified via multiple
 * delivery channels (Email and SMS or WhatsApp).
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\eSign\Client\ApiException;
use DocuSign\Services\Examples\eSignature\MultipleDeliveryService;
use DocuSign\Services\ManifestService;

class EG046MultipleDelivery extends eSignBaseController
{
    const EG = 'eg046';            # reference (and url) for this example
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
        
        try {
            $envelopeId = MultipleDeliveryService::multipleDelivery(
                $this->args,
                $this->clientService,
                $this::DEMO_DOCS_PATH,
                $GLOBALS['DS_CONFIG']['doc_docx'],
                $GLOBALS['DS_CONFIG']['doc_pdf']
            );

            if ($envelopeId) {
                $_SESSION["envelope_id"] = $envelopeId["envelope_id"]; # Save for use by other examples
                                                                       # which need an envelope_id
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    null,
                    ManifestService::replacePlaceholders(
                        "{0}",
                        $envelopeId["envelope_id"],
                        $this->codeExampleText["ResultsPageText"]
                    )
                );
            }
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate(
                new ApiException($this->codeExampleText["CustomErrorTexts"][0]["ErrorMessage"])
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
            'signer_email' => $this->checkEmailInputValue($_POST['signer_email']),
            'signer_country_code' => $this->checkInputValues($_POST['countryCode']),
            'signer_phone_number' => $this->checkInputValues($_POST['phoneNumber']),
            'cc_name' => $this->checkInputValues($_POST['ccName']),
            'cc_email' => $this->checkEmailInputValue($_POST['cc_email']),
            'cc_country_code' => $this->checkInputValues($_POST['ccCountryCode']),
            'cc_phone_number' => $this->checkInputValues($_POST['ccPhoneNumber']),
            'deliveryMethod' => $this->checkInputValues($_POST['deliveryMethod']),
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
