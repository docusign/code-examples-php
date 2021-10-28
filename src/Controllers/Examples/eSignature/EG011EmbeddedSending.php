<?php

/**
 * Example 011: Use embedded sending: Remote signer, cc; envelope has three documents
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\EmbeddedSendingService;

class EG011EmbeddedSending extends eSignBaseController
{
    const EG = 'eg011'; # reference (and URL) for this example
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
        # 2. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $results = EmbeddedSendingService::embeddedSending($this->args, $this->clientService, $this::DEMO_DOCS_PATH);

        if ($results) {
            # Redirect the user to the NDSE view
            # Don't use an iFrame!
            # State can be stored/recovered using the framework's session or a
            # query parameter on the returnUrl
            header('Location: ' . $results["redirect_url"]);
            exit;
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
            'cc_email' => $this->checkEmailInputValue($_POST['cc_email']),
            'cc_name' => $this->checkInputValues($_POST['cc_name']),
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'starting_view' => $this->checkInputValues($_POST['starting_view']),
            'envelope_args' => $envelope_args,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];
    }
}
