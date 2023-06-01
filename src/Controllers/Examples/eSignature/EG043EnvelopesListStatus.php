<?php
/**
 * Example 043: Shared access code example
 */

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\SharedAccessService;

class EG043EnvelopesListStatus extends eSignBaseController
{
    const EG = "eg043/EnvelopesListStatus";            # reference (and url) for this example
    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->codeExampleText = $this->getPageText(static::EG);
        $this->checkDsToken();

        $listStatus = SharedAccessService::listEnvelopes(
            $this->clientService->apiClient,
            $_SESSION['ds_account_id'],
            $_SESSION['principal_user_id']);

        if ($listStatus != null && $listStatus->getEnvelopes() != null) {
            $this->clientService->showDoneTemplate(
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["ExampleName"],
                $this->codeExampleText["AdditionalPage"][1]["ResultsPageText"],
                json_encode($listStatus->__toString())
            );
        }

        $this->clientService->showDoneTemplate(
            $this->codeExampleText["ExampleName"],
            $this->codeExampleText["ExampleName"],
            $this->codeExampleText["AdditionalPage"][2]["ResultsPageText"]
        );
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Redirect the user to the signing
     *
     * @return void
     * @throws \DocuSign\eSign\Client\ApiException
     */
    public function createController(): void { }

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
