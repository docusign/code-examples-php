<?php
/**
 * Example 043: Shared access.
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\SharedAccessService;

class EG043AuthRequest extends eSignBaseController
{
    const EG = "eg043/AuthRequest";            // reference (and url) for this example
    const FILE = __FILE__;
    const TITLE = "Authenticate as the agent";

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

        try {
            $basePath = $_SESSION['ds_base_path'];
            $accessToken = $_SESSION['ds_access_token'];
            $accountId = $_SESSION['ds_account_id'];
            $agentUserId = $_SESSION['agent_user_id'];

            $userId = SharedAccessService::getCurrentUserInfo($basePath, $accessToken)[0]['sub'];

            SharedAccessService::createUserAuthorization(
                $basePath,
                $accessToken,
                $accountId,
                $userId,
                $agentUserId);

            $_SESSION['principal_user_id'] = strval($userId);
            $_SESSION['userflow_example_43'] = true;

            $this->clientService->showDoneTemplate(
                self::TITLE,
                self::TITLE,
                $this->codeExampleText["AdditionalPage"][0]["ResultsPageText"],
                null,
                "index.php?page=ds_logout"
            );
        } catch (ApiException $apiException) {
            $this->clientService->showDoneTemplate(
                self::TITLE,
                self::TITLE,
                $this->codeExampleText["AdditionalPage"][3]["ResultsPageText"],
                null,
                "index.php?page=eg043/AuthRequest"
            );
        }
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
