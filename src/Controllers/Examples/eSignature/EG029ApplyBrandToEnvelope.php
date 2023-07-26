<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\ApplyBrandToEnvelopeService;
use Example\Services\ManifestService;

class EG029ApplyBrandToEnvelope extends eSignBaseController
{
    const EG = 'eg029'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $brands = $this->clientService->getBrands($this->args);
        parent::controller(null, null, $brands);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();
        $envelope_id = $this->args['envelope_id'];
        # 1. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $envelopeId = ApplyBrandToEnvelopeService::applyBrandToEnvelope(
            $this->args,
            $this::DEMO_DOCS_PATH,
            $this->clientService,
            $GLOBALS['DS_CONFIG']['doc_docx'],
            $GLOBALS['DS_CONFIG']['doc_pdf']
        );

        if ($envelopeId) {
            # That need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders("{0}", $envelopeId["envelope_id"], $this->codeExampleText["ResultsPageText"])
            );
        }

        if (!$envelope_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this::EG),
                $this->routerService->getTitle($this::EG),
                $this::EG,
                ['envelope_ok' => false]
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
            'brand_id' => $this->checkInputValues($_POST['brand_id']),
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
