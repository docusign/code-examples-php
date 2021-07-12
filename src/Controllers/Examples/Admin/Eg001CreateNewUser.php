<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Client\ApiException;
use DocuSign\OrgAdmin\Model\NewUserResponse;
use DocuSign\OrgAdmin\Model\PermissionProfileRequest;
use Example\Controllers\AdminBaseController;

use DocuSign\OrgAdmin\Api\UsersApi;
use DocuSign\OrgAdmin\Model\NewUserRequestAccountProperties;
use NewUserRequest as GlobalNewUserRequest;

class Eg001CreateNewUser extends AdminBaseController
{
    const EG = 'aeg001'; # reference (and url) for this example

    const FILE = __FILE__;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $this->checkDsToken();

        // Call the worker method
        $results = $this->addActiveUser($this->organizationId, $this->args["envelope_args"]);

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Create a new user",
                "Admin API data response output:",
                "Results from Users:createUser:",
                json_encode(($results->__toString()))
            );
        }
    }

    /**
     * Method to add a new user to your organization.
     * @param $organizationId
     * @param $userData
     * @return NewUserResponse
     * @throws ApiException
     */
    private function addActiveUser($organizationId, $userData): NewUserResponse
    {
        $apiClient = $this->clientService->getApiClient();

        $usersApi = new UsersApi($apiClient);
        $accountId = $GLOBALS['DS_CONFIG']['account_id'];

        $permissionProfile = new PermissionProfileRequest([
            'id' => $GLOBALS['DS_CONFIG']['premissionProfile_id'],
            'name' => $GLOBALS['DS_CONFIG']['premissionProfile_name']
        ]);

        $accountInfo = new NewUserRequestAccountProperties([
            'id' => $accountId,
            'permission_profile' => $permissionProfile
        ]);

        $request = new GlobalNewUserRequest([
            'user_name' => $userData['Name'],
            'first_name' => $userData['FirstName'],
            'last_name' => $userData['LastName'],
            'email' => $userData['Email'],
            'default_account_id' => $accountId,
            'accounts' => array($accountInfo),
            'auto_activate_memberships' => false
        ]);

        return $usersApi->createUser($organizationId, $request);
    }

    /**
     * Get specific template arguments
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $envelope_args = [
            'Name' => $this->checkInputValues($_POST['Name']),
            'FirstName' => $this->checkInputValues($_POST['FirstName']),
            'LastName' => $this->checkInputValues($_POST['LastName']),
            'Email' => $this->checkInputValues($_POST['Email'])
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }

    private function checkInputValues($value)
    {
        return preg_replace('/([^\w \-@.,])+/', '', $value);
    }
}
