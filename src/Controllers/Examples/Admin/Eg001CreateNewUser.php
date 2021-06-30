<?php

namespace Example\Controllers\Examples\Admin;

use Example\Controllers\AdminBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;
use DocuSign\OrgAdmin\Api\UsersApi;
use DocuSign\OrgAdmin\Model\NewUserRequestAccountProperties;
use NewUserRequest as GlobalNewUserRequest;

class Eg001CreateNewUser extends AdminBaseController
{
    /** Admin client service */
    private $clientService;

    /** Router service */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg001";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * Check the access token and call the worker method
     * @return void
     * @throws ApiException for API problems.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            
            $organizationId = $GLOBALS['DS_CONFIG']['organization_id'];

            // Call the worker method
            $results = $this->addActiveUser($organizationId, $this->args["envelope_args"]);

            if ($results) {
                $this->clientService->showDoneTemplate(
                    "Create a new user",
                    "Admin API data response output:",
                    "Results from Users:createUser:",
                    json_encode(($results->__toString()))
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Method to add a new user to your organization.
     * @return \DocuSign\OrgAdmin\Model\NewUserResponse
     * @throws ApiException for API problems.
     */
    private function addActiveUser($organizationId, $userData): \DocuSign\OrgAdmin\Model\NewUserResponse
    {
        $apiClient = $this->clientService->getApiClient();

        $usersApi = new UsersApi($apiClient);
        $accountId = $GLOBALS['DS_CONFIG']['account_id'];

        $premissionProfile = new \DocuSign\OrgAdmin\Model\PermissionProfileRequest([
            'id' => $GLOBALS['DS_CONFIG']['premissionProfile_id'],
            'name' => $GLOBALS['DS_CONFIG']['premissionProfile_name']
        ]);

        $nacountInfo = new NewUserRequestAccountProperties([
            'id' => $accountId,
            'permission_profile' => $premissionProfile
        ]);

        $request = new GlobalNewUserRequest([
            'user_name' => $userData['Name'],
            'first_name' => $userData['FirstName'],
            'last_name' => $userData['LastName'],
            'email' => $userData['Email'],
            'default_account_id' => $accountId,
            'accounts' => array($nacountInfo),
            'auto_activate_memberships' => false
        ]);

        return $usersApi->createUser($organizationId, $request);
    }

    /**
     * Get specific template arguments
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $Name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['Name']);
        $FirstName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['FirstName']);
        $LastName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['LastName']);
        $Email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['Email']);
        $envelope_args = [
            'Name' => $Name,
            'FirstName' => $FirstName,
            'LastName' => $LastName,
            'Email' => $Email
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];

        return $args;
    }
}
