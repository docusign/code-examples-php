<?php

/**
 * Example 013: Use embedded signing from template with added document
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\AddDocToTemplateService;

class EG013AddDocToTemplate extends eSignBaseController
{
    const EG = 'eg013'; # reference (and URL) for this example
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
        $template_id = $this->args['envelope_args']['template_id'];
        if ($template_id) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $envelopeTemplate = AddDocToTemplateService::addDocToTemplate($this->args, $this->clientService);

            if ($envelopeTemplate) {
                $_SESSION["envelope_id"] = $envelopeTemplate["envelope_id"]; # Save for use by other examples
                # which need an envelope_id
                # Redirect the user to the signing
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl
                header('Location: ' . $envelopeTemplate["redirect_url"]);
                exit;
            }
        } else {
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
            'signer_client_id' => 1000,
            'cc_email' => $this->checkEmailInputValue($_POST['cc_email']),
            'cc_name' => $this->checkInputValues($_POST['cc_name']),
            'item' => $this->checkInputValues($_POST['item']),
            'quantity' => intval($this->checkInputValues($_POST['quantity'])),
            'template_id' => $template_id,
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
