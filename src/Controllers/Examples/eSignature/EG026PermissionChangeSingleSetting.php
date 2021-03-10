<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\PermissionProfile;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG026PermissionChangeSingleSetting extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg026"; # Reference (and URL) for this example

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
                $results = json_decode((string) $results, true);
                $this->clientService->showDoneTemplate(
                    "Changing setting in a permission profile",
                    "Changing setting in a permission profile",
                    "Setting of permission profile has been changed!<br/> 
Permission profile ID: {$results["permissionProfileId"]}.<br> Changed settings:.",
                    json_encode(json_encode($results))
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
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    # ***DS.snippet.0.start
    public function worker($args): PermissionProfile
    {
        # Step 3. Construct the request body
        $accounts_api = $this->clientService->getAccountsApi();
        $permission_profile = new PermissionProfile();
        $permission_profile->setSettings($args['permission_args']['settings']);

        try {
            # Step 4. Call the eSignature REST API
            $results = $accounts_api->updatePermissionProfile(
                $args['account_id'],
                $args['permission_args']['permission_profile_id'],
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
        $permission_profile_id = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['permission_profile_id']);
        $permissions_args = [
            'permission_profile_id' => $permission_profile_id,
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