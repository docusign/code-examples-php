<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\UseConditionalRecipientsService;

class EG034UseConditionalRecipients extends eSignBaseController
{
    const EG = 'eg034'; # reference (and URL) for this example
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
        $envelope_id = UseConditionalRecipientsService::useConditionalRecipients(
            $this->args,
            $this->clientService,
            $this::DEMO_DOCS_PATH
        );

        if ($envelope_id) {
            # That need an envelope_id
            $this->clientService->showDoneTemplate(
                "Use conditional recipients",
                "Use conditional recipients",
                "Envelope ID {$envelope_id} with the conditional
                        routing criteria has been created and sent to the first recipient!"
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
            'signer_2a_email' => $this->checkEmailInputValue($_POST['signer_2a_email']),
            'signer_2a_name' => $this->checkInputValues($_POST['signer_2a_name']),
            'signer_2b_email' => $this->checkEmailInputValue($_POST['signer_2b_email']),
            'signer_2b_name' => $this->checkInputValues($_POST['signer_2b_name']),
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
