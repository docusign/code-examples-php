<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\UnpauseSignatureWorkflowService;

class EG033UnpauseSignatureWorkflow extends eSignBaseController
{
    const EG = 'eg033'; # reference (and URL) for this example
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
        $this->checkDsToken();
        # 1. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $envelope_id = UnpauseSignatureWorkflowService::unpauseSignatureWorkflow($this->args, $this->clientService);

        if ($envelope_id) {
            $_SESSION['pause_envelope_id'] = false;
            $this->clientService->showDoneTemplate(
                "Envelope unpaused",
                "Envelope unpaused",
                "The envelope workflow has been resumed and the envelope
                         has been sent to a second recipient!<br/>
                         Envelope ID {$envelope_id}."
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
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'pause_envelope_id' => $_SESSION['pause_envelope_id']
        ];
    }
}
