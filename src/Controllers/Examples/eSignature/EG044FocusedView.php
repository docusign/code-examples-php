<?php
/**
 * Example 044: Used to generate an envelope and allow user to sign
 * it directly from the app without having to open an email.
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\FocusedViewService;

class EG044FocusedView extends eSignBaseController
{
    const EG = 'eg044';            # reference (and url) for this example
    const FILE = __FILE__;
    const EMBED = 'esignature/embed';
    private int $signer_client_id = 1000;

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
     * Check the token
     * Call the worker method
     * Redirect the user to the signing
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();
        $pdf_doc = $GLOBALS['DS_CONFIG']['doc_pdf'];
        $envelope_id_and_url = FocusedViewService::worker(
            $this->args,
            $this->clientService,
            self::DEMO_DOCS_PATH,
            $pdf_doc
        );

        if ($envelope_id_and_url) {
            $GLOBALS['twig']->display(
                self::EMBED . '.html',
                [
                    'common_texts' => $this->getCommonText(),
                    'integration_key' => $GLOBALS['DS_CONFIG']['ds_client_id'],
                    'envelope_id' => $envelope_id_and_url['envelope_id'],
                    'url' =>  $envelope_id_and_url['redirect_url']
                ]
            );
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
