<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\PauseSignatureWorkflowService;

class EG032PauseSignatureWorkflow extends eSignBaseController
{
    const EG = 'eg032'; # reference (and URL) for this example
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
        $envelope = PauseSignatureWorkflowService::pauseSignatureWorkflow(
            $this->args,
            $this->clientService,
            $this::DEMO_DOCS_PATH
        );

        if ($envelope) {
            $_SESSION["pause_envelope_id"] = $envelope["envelope_id"];
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode($envelope->__toString())
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
            'signer1_email' => $this->checkEmailInputValue($_POST['signer1_email']),
            'signer1_name' => $this->checkInputValues($_POST['signer1_name']),
            'signer2_email' => $this->checkEmailInputValue($_POST['signer2_email']),
            'signer2_name' => $this->checkInputValues($_POST['signer2_name']),
            'status' => "Sent",
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
