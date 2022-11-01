<?php

/**
 * Example 015: Get an envelope's tab (field) data
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\EnvelopeTabDataService;

class EG015EnvelopeTabData extends eSignBaseController
{
    const EG = 'eg015'; # reference (and URL) for this example
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
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();
        $envelope_id = $this->args['envelope_id'];
        if ($envelope_id) {
            # 2. Call the worker method
            $envelopeFormData = EnvelopeTabDataService::envelopeTabData($this->args, $this->clientService);

            if ($envelopeFormData) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $envelopeFormData = json_decode((string)$envelopeFormData, true);
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode(json_encode($envelopeFormData))
                );
            }
        } else {
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
        $envelope_id = $_SESSION['envelope_id'] ?? false;
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id
        ];
    }
}
