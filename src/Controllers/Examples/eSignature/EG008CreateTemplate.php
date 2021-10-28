<?php

/**
 * Example 008: create a template if it doesn't already exist
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\CreateTemplateService;

class EG008CreateTemplate extends eSignBaseController
{
    const EG = 'eg008'; # reference (and URL) for this example
    const FILE = __FILE__;
    private string $template_name = 'Example Signer and CC template';

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
        $results = CreateTemplateService::createTemplate($this->args, $this->template_name, $this::DEMO_DOCS_PATH, $this->clientService);
        if ($results) {
            $_SESSION["template_id"] = $results["template_id"]; # Save for use by other examples
            $msg = $results['created_new_template'] ? "The template has been created!" :
                        "Done. The template already existed in your account.";

            $this->clientService->showDoneTemplate(
                "Template results",
                "Template results",
                "{$msg}<br/>Template name: {$results['template_name']}, 
                            ID {$results['template_id']}."
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
        return $this->getDefaultTemplateArgs();
    }
}
