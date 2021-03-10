<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\PermissionProfile;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG024PermissionCreate extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg024"; # Reference (and URL) for this example

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
        parent::controller(
            $this->eg,
            $this->routerService,
            basename(__FILE__),
            null,
            null,
            $permission_profiles
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
            $results = $this->worker($this->args);

            if ($results) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Creating a permission profile",
                    "Creating a permission profile",
                    "The permission profile has been created!<br/> 
Permission profile ID {$results["permission_profile_id"]}."
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
     * @param  array $args
     * @return PermissionProfile $results
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    # ***DS.snippet.0.start
    public function worker($args): PermissionProfile
    {

        # Step 3. Construct the request body
        $accounts_api = $this->clientService->getAccountsApi();
        $permission_profile = new PermissionProfile($args['permission_args']);

        try {
            # Step 4. Call the eSignature REST API
            $results = $accounts_api->createPermissionProfile(
                $args['account_id'],
                $permission_profile
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
        $permission_profile_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['permission_profile_name']);
        $permissions_args = [
            'permission_profile_name' => $permission_profile_name,
            'settings' => $this->getSettings(),
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