<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\NewUserResponse;
use DocuSign\Admin\Model\PermissionProfileRequest;
use Example\Controllers\AdminBaseController;

use DocuSign\Admin\Api\UsersApi;
use DocuSign\Admin\Model\NewUserRequestAccountProperties;
use DocuSign\Admin\Model\NewUserRequest as GlobalNewUserRequest;

class Eg001CreateNewUser extends AdminBaseController
{
    const EG = 'aeg001'; # reference (and url) for this example

    const FILE = __FILE__;

    //private $eg = "aeg001";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     * @return void
     * @throws ApiException
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller();

        $this->checkDsToken();

        // Step 3 start
        $eSignProductId = $clmProductId = $clmPermissionProfiles = $eSignPermissionProfiles = "";
        $ppReq = $this->clientService->permProfilesApi();
        $permissionProfiles = $ppReq->getProductPermissionProfiles($this->organizationId, $GLOBALS['DS_CONFIG']['account_id']);

        foreach ($permissionProfiles['product_permission_profiles'] as $item) {
            if ($item['product_name'] ==  "CLM") {
                $clmPermissionProfiles = $item;
            }
            else {
                $eSignPermissionProfiles = $item;
            }
        }
        // Step 3 end

        $preFill = [
            'clmPermissionProfiles' => $clmPermissionProfiles,
            'eSignPermissionProfiles' => $eSignPermissionProfiles,
        ];
        parent::controller($preFill);
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
            'id' => $userData['eSignPermissionProfileId']
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
        $clmPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmPermissionProfileId"]);
        $esignPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["eSignPermissionProfileId"]);
        $envelope_args = [
            'Name' => $this->checkInputValues($_POST['Name']),
            'FirstName' => $this->checkInputValues($_POST['FirstName']),
            'LastName' => $this->checkInputValues($_POST['LastName']),
            'Email' => $this->checkInputValues($_POST['Email']),
            'clm_permission_profile_id' => $clmPermissionProfileId,
            'esign_permission_profile_id' => $esignPermissionProfileId,
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
