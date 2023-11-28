<?php

namespace DocuSign\Controllers\Examples\Connect;

use DocuSign\Controllers\ConnectBaseController;
use DocuSign\Services\Examples\Connect\ValidateUsingHmacService;
use DocuSign\Services\ManifestService;

class Eg001ValidateUsingHmac extends ConnectBaseController
{
    const EG = 'con001'; # reference (and url) for this example

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
     * Check the access token and call the worker method
     * @return void
     */
    public function createController(): void
    {
        $args = $this->getTemplateArgs();
        $hashedOutput = ValidateUsingHmacService::computeHash(
            $args['envelope_args']['HmacSecret'],
            $args['envelope_args']['JsonPayload']
        );

        $this->clientService->showDoneTemplateFromManifest(
            $this->codeExampleText,
            null,
            ManifestService::replacePlaceholders('{0}', $hashedOutput, $this->codeExampleText['ResultsPageText'])
        );
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $envelope_args = [
            'HmacSecret' => $_POST['HmacSecret'],
            'JsonPayload' => $_POST['JsonPayload'],
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
