<?php

/**
 * Example 003: List envelopes whose status has changed in the last 10 days
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\ListEnvelopesService;

class EG003ListEnvelopes extends eSignBaseController
{
    const EG = 'eg003'; # reference (and URL) for this example
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
        $results = ListEnvelopesService::listEnvelopes($this->args, $this->clientService);

        if ($results) {
            # results is an object that implements ArrayAccess. Convert to a regular array:
            $results = json_decode((string)$results, true);
            $this->clientService->showDoneTemplate(
                "Envelope list",
                "List envelopes results",
                "Results from the Envelopes::listStatusChanges method:",
                json_encode(json_encode($results))
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
