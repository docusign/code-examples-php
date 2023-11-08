<?php

/**
 * Example 039: In person signing.
*/

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\InPersonSigningService;
use DocuSign\eSign\Client\ApiException;

class EG039InPersonSigning extends eSignBaseController
{
    const EG = 'eg039';
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
     * 3. Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        // Perhaps these can help with a use case?
        // $hostEmail = $this->clientService->getAuthenticatedUserEmail($this->args["ds_access_token"]);
        // $hostName = $this->clientService->getAuthenticatedUserName($this->args["ds_access_token"]);
        try {
            $returnUrl = InPersonSigningService::worker(
                $this->args["account_id"],
                $this->args["signer_name"],
                $this->clientService,
                self::DEMO_DOCS_PATH
            );

            if ($returnUrl) {
                header('Location: ' . $returnUrl);
                exit;
            }
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'signer_name' => $this->checkInputValues($_POST['signer_name']),
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];
    }
}
