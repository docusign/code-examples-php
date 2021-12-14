<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\BulkSendEnvelopesService;

class EG031BulkSendEnvelopes extends eSignBaseController
{
    const EG = "eg031"; # Reference (and URL) for this example
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
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $this->checkDsToken();

        # 1. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $bulkSendBatchStatus = json_decode(
            BulkSendEnvelopesService::bulkSendEnvelopes($this->args, $this->clientService, self::DEMO_DOCS_PATH),
            true
        );

        if ($bulkSendBatchStatus) {
            # That need an envelope_id
            $this->clientService->showDoneTemplate(
                "Bulk send envelopes",
                "Bulk send envelopes",
                "Results from BulkSend:getBulkSendBatchStatus method:",
                json_encode(json_encode($bulkSendBatchStatus))
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
        $signers = [
            [
                'signer_email' => $this->checkEmailInputValue($_POST['signer_email_1']),
                'signer_name' => $this->checkInputValues($_POST['signer_name_1']),
                'cc_email' => $this->checkEmailInputValue($_POST['cc_email_1']),
                'cc_name' => $this->checkInputValues($_POST['cc_name_1'])
            ],
            [
                'signer_email' => $this->checkEmailInputValue($_POST['signer_email_2']),
                'signer_name' => $this->checkInputValues($_POST['signer_name_2']),
                'cc_email' => $this->checkEmailInputValue($_POST['cc_email_2']),
                'cc_name' => $this->checkInputValues($_POST['cc_name_2'])
            ]
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'signers' => $signers
        ];
    }
}