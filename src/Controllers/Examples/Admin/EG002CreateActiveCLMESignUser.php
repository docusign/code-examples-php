<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Model\ProductPermissionProfileRequest;
use DocuSign\OrgAdmin\Client\ApiException;
use DocuSign\OrgAdmin\Model\AddUserResponse;
use DocuSign\OrgAdmin\Model\DSGroupRequest;
use DocuSign\OrgAdmin\Model\NewMultiProductUserAddRequest;
use Example\Controllers\AdminApiBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;
use Exception;

class EG002CreateActiveCLMESignUser extends AdminApiBaseController
{
    /** AdminClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg002";       # Reference (and URL) for this example 
    
    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {

        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();

        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {

        // Step 3 start       
        $eSignProductId = $clmProductId = $clmPermissionProfiles = $eSignPermissionProfiles = "";
        $ppReq = $this->clientService->permProfilesApi();
        $permissionProfiles = $ppReq->getProductPermissionProfiles($this->args["organization_id"], $this->args["account_id"]);       
        
        foreach ($permissionProfiles['product_permission_profiles'] as $item) {
            if ($item['product_name'] ==  "CLM") {
                $clmPermissionProfiles = $item;
                $clmProductId = $item["product_id"];

            }
            else {
                $eSignPermissionProfiles = $item;
                $eSignProductId = $item["product_id"];
            }
        }
        // Step 3 end

        // Step 4 start
        $dsgReq = $this->clientService->adminGroupsApi();
        $dsgRes = $dsgReq->getDSGroups($this->args["organization_id"], $this->args["account_id"]);
        $dsGroups = $dsgRes["ds_groups"];
        // Step 4 end
        } 
        else {
            $this->clientService->needToReAuth($this->eg);
        }
    
        $preFill = [
            'clmPermissionProfiles' => $clmPermissionProfiles,
            'eSignPermissionProfiles' => $eSignPermissionProfiles,
            'dsGroups' => $dsGroups,
            'clmProductId' => $clmProductId,
            'eSignProductId' => $eSignProductId
        ];
        parent::controller(
            $this->eg, 
            $this->routerService, 
            basename(__FILE__),
            $preFill
        );



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

                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Create active user for CLM and eSignature",
                    "Create active user for CLM and eSignature",
                    "Results from Users::addUsers_0 method:",
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
     * @return Object AddUserResponse
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */

    public function worker($args): AddUserResponse
    {

        # Step 5 Start
        $admin_api = $this->clientService->getUsersApi();
        
        $eSignProfile = new ProductPermissionProfileRequest([
            "permission_profile_id" => $args["esign_permission_profile_id"],
            "product_id" => $args["esign_product_id"]
        ]);

        $clmProfile = new ProductPermissionProfileRequest([
            "permission_profile_id" => $args["clm_permission_profile_id"],
            "product_id" => $args["clm_product_id"]
        ]);

        $dsGroups = new DSGroupRequest([
            'ds_group_id' => $args["group_id"]
        ]);

        $request = new NewMultiProductUserAddRequest([
            'user_name'=> $args["user_id"],
            'first_name' => $args["first_name"],
            'last_name' => $args["last_name"],
            'auto_activate_memberships' => true,
        ]);
        $request->setProductPermissionProfiles([$clmProfile, $eSignProfile]);
        $request->setEmail($args["email"]);
        $request->setDsGroups([$dsGroups]);
        # Step 5 end
        try {

            # Step 6 start
            $results = $admin_api->addUsers_0($args["organization_id"], $args["account_id"], $request);
            # Step 6 end

        } catch (Exception $e) {
            // var_dump($e);
            $GLOBALS['twig']->display('error.html', [
                    'error_code' => $e->getCode(),
                    'error_message' =>  $e->getMessage()]
            );
            exit;
            }              
        return  $results;
    }

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
        $email = preg_replace('/([^\w +\-\@\.\,])+/', '', $_POST["email"]);
        $clmProductId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmProductId"]);
        $esignProductId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["eSignProductId"]);
        $clmPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmPermissionProfileId"]);
        $esignPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["eSignPermissionProfileId"]);
        $dsGroupId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["dsGroupId"]);


        $args = [
            'organization_id' => $GLOBALS['DS_CONFIG']['organization_id'],
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