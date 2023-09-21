<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\ApplyBrandToTemplateService;
use Example\Services\ManifestService;

class EG030ApplyBrandToTemplate extends eSignBaseController
{
    const EG = 'eg030'; # reference (and URL) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance
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
        $template_id = $this->args['envelope_args']['template_id'];
        
        # 2. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $envelopeId = ApplyBrandToTemplateService::applyBrandToTemplate(
            $this->args,
            $this->clientService
        );

        if ($envelopeId) {
            # That need an envelope_id
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders("{0}", $envelopeId["envelope_id"], $this->codeExampleText["ResultsPageText"])
            );
        }
        if (!$template_id) {
            $this->clientService->envelopeNotCreated(
                basename(__FILE__),
                $this->routerService->getTemplate($this::EG),
                $this->routerService->getTitle($this::EG),
                $this::EG,
                ['template_ok' => false]
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
        $template_id = $_SESSION['template_id'] ?? false;
        $envelope_args = [
            'signer_email' => $this->checkEmailInputValue($_POST['signer_email']),
            'signer_name' => $this->checkInputValues($_POST['signer_name']),
            'cc_email' => $this->checkEmailInputValue($_POST['cc_email']),
            'cc_name' => $this->checkInputValues($_POST['cc_name']),
            'brand_id' => $this->checkInputValues($_POST['brand_id']),
            'template_id' => $template_id
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
