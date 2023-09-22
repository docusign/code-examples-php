<?php

/**
 * Example 003: List envelopes whose status has changed in the last 10 days
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\ListEnvelopesService;

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
        $envelopesInformation = ListEnvelopesService::listEnvelopes($this->args, $this->clientService);

        if ($envelopesInformation) {
            # results is an object that implements ArrayAccess. Convert to a regular array:
            $envelopesInformation = json_decode((string)$envelopesInformation, true);
            $this->clientService->showDoneTemplateFromManifest(
                $this->codeExampleText,
                json_encode(json_encode($envelopesInformation))
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
