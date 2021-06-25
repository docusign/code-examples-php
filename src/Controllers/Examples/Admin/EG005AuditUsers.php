<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\OrgAdmin\Api\UsersApi\GetUserProfilesOptions;
use DocuSign\OrgAdmin\Api\UsersApi\GetUsersOptions;
use DocuSign\OrgAdmin\Client\ApiException;
use Example\Controllers\AdminApiBaseController;
use Example\Services\AdminApiClientService;
use Example\Services\RouterService;
use Exception;

class EG005AuditUsers extends AdminApiBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "aeg005";       # Reference (and URL) for this example



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

                $results = json_decode((string)$results, true);
                $this->clientService->showDoneTemplate(
                    "Audit users",
                    "Audit users",
                    "Results from Users::getUserProfiles method:",
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
    public function worker($args): array
    {

        $results = [];

        $admin_api = $this->clientService->getUsersApi();
        $options = New GetUsersOptions();
        $options->setAccountId($args["account_id"]);

        # Here we set the from_date to filter envelopes for the last 10 days
        # Use ISO 8601 date format
        $from_date = date("c", (time() - (10 * 24 * 60 * 60)));
        $options->setLastModifiedSince($from_date);

        try {
            # Step 3 start
            $modifiedUsers = $admin_api->getUsers($args["organization_id"], $options);

            foreach ($modifiedUsers["users"] as $user) {
                $profileOptions = New GetUserProfilesOptions();
                $profileOptions->setEmail($user["email"]);
                $res = $admin_api->getUserProfiles($args["organization_id"], $profileOptions);
                array_push($results, $res);
            }
            # Step 3 end
        } catch (Exception $e) {
            var_dump($e);
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
        
        $args = [
            'organization_id' => $GLOBALS['DS_CONFIG']['organization_id'],
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'] 
        ];

        return $args;
    }
}