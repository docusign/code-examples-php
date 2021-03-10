<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Group;
use DocuSign\eSign\Model\GroupInformation;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG025PermissionSetUserGroup extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg025"; # Reference (and URL) for this example

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        $permission_profiles = $this->clientService->getPermissionsProfiles($this->args);
        $groups = $this->clientService->getGroups($this->args);
        parent::controller(
            $this->eg,
            $this->routerService,
            basename(__FILE__),
            null,
            null,
            $permission_profiles,
            $groups
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
            # 1. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = json_decode($this->worker($this->args), true);
            
            if ($results) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Set a permission profile to a group of users",
                    "Set a permission profile to a group of users",
                    "The permission profile has been set!<br/>
        Permission profile id: {$results['groups'][0]['permissionProfileId']}<br/>
        Group id: {$results['groups'][0]['groupId']}"
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return string
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    # ***DS.snippet.0.start
    public function worker($args): string
    {

        # Step 3. Construct your request body
        $groups_api = $this->clientService->getGroupsApi();
        $group = new Group($args['permission_args']);
        $group_information = new GroupInformation(['groups' => [$group]]);

        try {
            # Step 4. call the eSignature REST API
            $results = $groups_api->updateGroups(
                $args['account_id'],
                $group_information
            );
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $permission_profile_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['permission_profile_id']);
        $group_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['group_id']);
        $permissions_args = [
            'permission_profile_id' => $permission_profile_id,
            'group_id' => $group_id,
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'permission_args' => $permissions_args
        ];

        return $args;
    }
}