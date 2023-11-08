<?php

/**
 * Example 016: Set optional and locked field values and an envelope custom field value
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\SetTabValuesService;

class EG016SetTabValues extends eSignBaseController
{
    const EG = 'eg016'; # reference (and URL) for this example
    const FILE = __FILE__;
    private int $signer_client_id = 1000; # Used to indicate that the signer will use embedded
                                      # signing. Represents the signer's userId within your application.

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
        # 2. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $envelopeResponse = SetTabValuesService::setTabValues($this->args, $this::DEMO_DOCS_PATH, $this->clientService);

        if ($envelopeResponse) {
            $_SESSION["envelope_id"] = $envelopeResponse["envelope_id"]; # Save for use by other examples
                                                                # which need an envelope_id

            # Redirect the user to the embedded signing
            # Don't use an iFrame!
            # State can be stored/recovered using the framework's session or a
            # query parameter on the returnUrl (see the makerecipient_view_request method)
            header('Location: ' . $envelopeResponse["redirect_url"]);
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
