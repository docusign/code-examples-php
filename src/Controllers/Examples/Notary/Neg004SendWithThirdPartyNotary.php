<?php

namespace DocuSign\Controllers\Examples\Notary;

use DocuSign\Controllers\NotaryApiBaseController;
use DocuSign\Services\Examples\Notary\SendWithThirdPartyNotaryService;
use DocuSign\WebForms\Client\ApiException;
use DocuSign\Services\ManifestService;

class Neg004SendWithThirdPartyNotary extends NotaryApiBaseController
{
    const EG = "n004"; # reference (and URL) for this example
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
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            "signer_email" => $this->checkEmailInputValue($_POST["signer_email"]),
            "signer_name" => $this->checkInputValues($_POST["signer_name"]),
            "account_id" => $_SESSION["ds_account_id"],
            "base_path" => $_SESSION["ds_base_path"],
            "ds_access_token" => $_SESSION["ds_access_token"]
        ];
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Return envelope id
     *
     * @return void
     * @throws ApiException
     * @throws \DocuSign\eSign\Client\ApiException
     */
    protected function createController(): void
    {
        $this->checkDsToken();

        $envelopeResponse = SendWithThirdPartyNotaryService::sendWithNotary(
            $this->args["signer_email"],
            $this->args["signer_email"],
            $this->clientService->getEnvelopeApi(),
            $this->args["account_id"],
            self::DEMO_DOCS_PATH
        );
        

        if ($envelopeResponse) {
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
}
