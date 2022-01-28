<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\CreateActiveCLMESignUserService;

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

        $this->checkDsToken();

        try
        {
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
        catch (ApiException $e)
        {
            $this->clientService->showErrorTemplate($e);
        }        
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
        $this->checkDsToken();

        # Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $addUsersResponse = CreateActiveCLMESignUserService::createActiveCLMESignUser(
            $this->clientService,
            $this->args,
            $this->orgId
        );

        if ($addUsersResponse) {
            $addUsersResponse = json_decode((string)$addUsersResponse, true);
            $this->clientService->showDoneTemplate(
                "Create a new active user for CLM and eSignature",
                "Create a new active user for CLM and eSignature",
                "Results from MultiProductUserManagement:addOrUpdateUser method:",
                json_encode(json_encode($addUsersResponse))
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
        $userName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["userName"]);
        $firstName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["firstName"]);
        $lastName = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["lastName"]);
        $email = preg_replace('/([^\w +\-\@\.\,])+/', '', $_POST["email"]);
        $clmProductId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmProductId"]);
        $esignProductId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["eSignProductId"]);
        $clmPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["clmPermissionProfileId"]);
        $esignPermissionProfileId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["eSignPermissionProfileId"]);
        $dsGroupId = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST["dsGroupId"]);


        return [
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
    }
}
