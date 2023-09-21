<?php
/**
 * Example 001: Use embedded signing
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\CFREmbeddedSigningService;

class EG041CFREmbeddedSigning extends eSignBaseController
{
    const EG = "eg041";            # reference (and url) for this example
    const FILE = __FILE__;
    private int $signer_client_id = 1000; # Used to indicate that the signer will use embedded
    # Signing. Represents the signer's userId within your application.

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
     * 3. Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        # 1. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $envelopeIdAndReturnUrl = CFREmbeddedSigningService::worker(
            $this->args,
            $this->clientService,
            self::DEMO_DOCS_PATH
        );

        if ($envelopeIdAndReturnUrl) {
            # Redirect the user to the embedded signing
            # Don't use an iFrame!
            # State can be stored/recovered using the framework's session or a
            # query parameter on the returnUrl (see the make recipient_view_request method)
            header('Location: ' . $envelopeIdAndReturnUrl["redirect_url"]);
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
            'signer_email' => $this->checkInputValues($_POST['signer_email']),
            'signer_name' => $this->checkInputValues($_POST['signer_name']),
            'phone_number' => $_POST['phone_number'],
            'country_code' => $_POST['country_code'],
            'signer_client_id' => $this->signer_client_id,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }
}
