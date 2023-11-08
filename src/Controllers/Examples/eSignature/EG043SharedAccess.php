<?php
/**
 * Example 043: Shared access code example
 */

namespace DocuSign\Controllers\Examples\eSignature;

use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\Examples\eSignature\SharedAccessService;

class EG043SharedAccess extends eSignBaseController
{
    const EG = "eg043";            # reference (and url) for this example
    const FILE = __FILE__;
    const REDIRECT = "index.php?page=eg043/AuthRequest";
    const AGENT_USER_CREATED = "Agent user created";

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
     * @throws \DocuSign\eSign\Client\ApiException
     */
    public function createController(): void
    {
        $this->checkDsToken();
        $accountId = $this->args["account_id"];
        $usersApi = $this->clientService->getUsersApi();
        $agentEmail = $this->args["form_data"]["agent_email"];

        $userInformation = SharedAccessService::getUserInfo(
            $usersApi,
            $accountId,
            $agentEmail
        );

        if ($userInformation == null) {
            $user = SharedAccessService::shareAccess(
                $usersApi,
                $accountId,
                $agentEmail,
                $this->args["form_data"]["agent_name"],
                $this->args["form_data"]["activation_code"]
            );

            $_SESSION['agent_user_id'] = strval($user->getNewUsers()[0]->getUserId());

            $this->clientService->showDoneTemplate(
                self::AGENT_USER_CREATED,
                self::AGENT_USER_CREATED,
                $this->codeExampleText["ResultsPageText"],
                json_encode($user->__toString()),
                self::REDIRECT
            );
        } else {
            $_SESSION['agent_user_id'] = strval($userInformation->getUserId());

            $this->clientService->showDoneTemplate(
                self::AGENT_USER_CREATED,
                self::AGENT_USER_CREATED,
                $this->codeExampleText["ResultsPageText"],
                json_encode($userInformation->__toString()),
                self::REDIRECT
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
        $formData = [
            'agent_email' => $this->checkEmailInputValue($_POST['agent_email']),
            'agent_name' => $this->checkInputValues($_POST['agent_name']),
            'activation_code' => $_POST['activation_code'],
        ];
        
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'form_data' => $formData
        ];
    }
}
