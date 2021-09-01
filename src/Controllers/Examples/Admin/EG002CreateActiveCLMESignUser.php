<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\AddUserResponse;
use DocuSign\Admin\Model\DSGroupRequest;
use DocuSign\Admin\Model\NewMultiProductUserAddRequest;
use DocuSign\Admin\Model\ProductPermissionProfileRequest;
use Example\Controllers\AdminApiBaseController;

class EG002CreateActiveCLMESignUser extends AdminApiBaseController
{
    const EG = 'aeg002'; # reference (and url) for this example

    const FILE = __FILE__;

    private $orgId;

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {

        // Step 3 start       
        $eSignProductId = $clmProductId = $clmPermissionProfiles = $eSignPermissionProfiles = "";
        $this->orgId = $this->clientService->getOrgAdminId($this->args);
        $ppReq = $this->clientService->permProfilesApi();
        $permissionProfiles = $ppReq->getProductPermissionProfiles($this->orgId, $this->args["account_id"]);       
   

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
        $dsgRes = $dsgReq->getDSGroups($this->orgId, $this->args["account_id"]);
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
                    "Create a new active user for CLM and eSignature",
                    "Create a new active user for CLM and eSignature",
                    "Results from MultiProductUserManagement:addOrUpdateUser method:",
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
            $results = $admin_api->addOrUpdateUser($this->orgId, $args["account_id"], $request);
            # Step 6 end

        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            }              
        return  $results;
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
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
