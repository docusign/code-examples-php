<?php
/**
 * Example 001: Use embedded signing
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\DocumentGenerationService;
use Example\Services\ManifestService;

class EG042DocumentGeneration extends eSignBaseController
{
    const EG = "eg042";            # reference (and url) for this example
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

        $envelopeId = DocumentGenerationService::worker(
            $this->args,
            $this->clientService,
            self::DEMO_DOCS_PATH
        );

        if ($envelopeId) {
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                null,
                ManifestService::replacePlaceholders(
                    "{0}",
                    $envelopeId,
                    $this->codeExampleText["ResultsPageText"]
                )
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
        $form_data = [
            'candidate_email' => $this->checkInputValues($_POST['candidate_email']),
            'candidate_name' => $this->checkInputValues($_POST['candidate_name']),
            'manager_name' => $this->checkInputValues($_POST['manager_name']),
            'job_title' => $_POST['job_title'],
            'salary' => $_POST['salary'],
            'start_date' => $_POST['start_date'],
        ];
        
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'form_data' => $form_data
        ];
    }
}
