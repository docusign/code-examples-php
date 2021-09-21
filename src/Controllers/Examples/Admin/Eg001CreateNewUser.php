<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\GroupRequest;
use DocuSign\Admin\Model\NewUserRequest as GlobalNewUserRequest;
use DocuSign\Admin\Model\NewUserRequestAccountProperties;
use DocuSign\Admin\Model\NewUserResponse;
use DocuSign\Admin\Model\PermissionProfileRequest;
use Example\Controllers\AdminApiBaseController;
use Example\Services\SignatureClientService;

class EG001CreateNewUser extends AdminApiBaseController
{
    const EG = 'aeg001'; # reference (and url) for this example

    const FILE = __FILE__;

    private $orgId;

    //private $eg = "aeg001";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     * @return void
     * @throws ApiException
     */
    public function __construct()
    {
        parent::__construct();

        $this->checkDsToken();

        $this->orgId = $this->clientService->getOrgAdminId($this->args);

        try {
            $signatureClientService = new SignatureClientService($this->args);
            $permission_profiles = $signatureClientService->getPermissionsProfiles($this->args);
            $groupsObj = $signatureClientService->getGroups($this->args);
            $args = [
                'permission_profiles'=> $permission_profiles,
                'groups'=> $groupsObj
            ];
            parent::controller($args);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
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
        $args = $this->getTemplateArgs();
        $results = $this->addActiveUser($args["envelope_args"]);

        if ($results) {
            $this->clientService->showDoneTemplate(
                "Create a new active eSignature user",
                "Create a new active eSignature user",
                "Results from eSignUserManagement:createUser method:",
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
    private function addActiveUser($userData): NewUserResponse
    {

        # Step 3 start
$usersApi = $this->clientService->getUsersApi();
$accountId = $_SESSION['ds_account_id'];
$permissionProfile = new PermissionProfileRequest([
    'id' => $userData['permission_profile_id']
]);

$group = new GroupRequest([
    'id' => (int) $userData['group_id']
]);

$accountInfo = new NewUserRequestAccountProperties([
    'id' => $accountId,
    'permission_profile' => $permissionProfile,
    'groups' => [ $group ]
]);

$request = new GlobalNewUserRequest([
    'user_name' => $userData['Name'],
    'first_name' => $userData['FirstName'],
    'last_name' => $userData['LastName'],
    'email' => $userData['Email'],
    'default_account_id' => $accountId,
    'accounts' => array($accountInfo),
    'auto_activate_memberships' => true
]);
        # Step 3 end

        # Step 4 start
        return $usersApi->createUser($this->orgId, $request);
        # Step 4 end
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
            'Email' => $this->checkInputValues($_POST['Email']),
            'permission_profile_id' => $this->checkInputValues($_POST["permission_profile_id"]),
            'group_id' => $this->checkInputValues($_POST["group_id"])
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];
    }

    /**
     * @param $value
     * @return mixed
     */
    private function checkInputValues($value): string
    {
        return preg_replace('/([^\w \-\@\.\,])+/', '', $value);
    }
}
