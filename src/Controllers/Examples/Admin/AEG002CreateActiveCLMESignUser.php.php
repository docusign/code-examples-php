<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Api\ProductPermissionProfilesApi;
use DocuSign\OrgAdmin\Model\ProductPermissionProfileRequest;
use DocuSign\OrgAdmin\Client\ApiException;
use \DocuSign\OrgAdmin\Model\NewUserRequest;
use Example\Controllers\AdminApiBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;



class EG002CreateActiveCLMESignUser extends AdminApiBaseController
{
    /** AdminClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg002";       # Reference (and URL) for this example
    private $organizationId; 
    
    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {
                # Success if there's an envelope Id and the brand name isn't a duplicate
                $this->clientService->showDoneTemplate(
                    "Create active user for CLM and eSignature",
                    "Create active user for CLM and eSignature",
                    "Results from Users::addUsers_0 method",
                    json_encode(json_encode($results))
                );
            }
        } 
        else {
            $this->clientService->needToReAuth($this->eg);
        }
    }





    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    # ***DS.snippet.0.start
    public function worker($args): array
    {

        # Step 3 Start
        $admin_api = $this->clientService->getUsersApi();
        
        $eSignprofile = new ProductPermissionProfileRequest([
            "permission_profile_id" => $args["esign_permission_profile_id"],
            "product_id" => $args["esign_product_id"]
        ]);

        $clmProfile = new ProductPermissionProfileRequest([
            "permission_profile_id" => $args["clm_permission_profile_id"],
            "product_id" => $args["clm_product_id"]
        ]);
        $profiles = new ProductPermissionProfilesApi();
        $profiles->addUserProductPermissionProfiles($this->organization_id, $args["account_id"], $args["user_id"], $eSignprofile);
        $profiles->addUserProductPermissionProfiles($this->organization_id, $args["account_id"], $args["user_id"], $clmProfile);
        
        $request = new NewUserRequest([
            'user_name'=> $args["userName"],
            'first_name' => $args["firstName"],
            'last_name' => $args["lastName"],
            'email' => $args["email"],
            'auto_activate_memberships' => true,
            'product_permission_profiles' => $profiles,
            'ds_groups' =>
                   [ 'ds_group_id' => $args["group_id"] 
            ]
        ]);


        try {
            # Step 4 Call the eSignature REST API
            $results = $admin_api->createUser($this->organizationId, $request);
        } catch (ApiException $e) {
            $error_code = $e->getResponseBody()->errorCode;
            $error_message = $e->getResponseBody()->message;
            $GLOBALS['twig']->display('error.html', [
                    'error_code' => $error_code,
                    'error_message' => $error_message]
            );
            exit;
            }              
        return [ $results ];
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {

        $userName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["userName"]);
        $firstName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["firstName"]);
        $lastName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["lastName"]);
        $email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["email"]);
        $clmProductId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmProductId"]);
        $esignProductId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["esignProductId"]);
        $clmPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmPermissionProfileId"]);
        $esignPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["esignPermissionProfileId"]);
        $dsGroupId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["dsGroupId"]);


        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'], 
            'user_id' => $userName, 
            'first_name' => $firstName, 
            'last_name' => $lastName, 
            'email' => $email, 
            'clm_product_id' => $clmProductId, 
            'esign_product_id' => $esignProductId, 
            'clm_permission_profile_id' => $clmPermissionProfileId, 
            'esign_permission_profile_id' => $esignPermissionProfileId, 
            'group_id' => $dsGroupId 

        ];

        return $args;
    }
}