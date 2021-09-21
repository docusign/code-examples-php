<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Api\UsersApi\GetUserProfilesOptions;
use DocuSign\Admin\Api\UsersApi\GetUsersOptions;
use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\UsersDrilldownResponse;
use Example\Controllers\AdminApiBaseController;

class EG005AuditUsers extends AdminApiBaseController
{
    const EG = 'aeg005'; # reference (and url) for this example

    const FILE = __FILE__;

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        parent::controller();
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
                $this->clientService->showDoneTemplate(
                    "Audit users",
                    "Audit users",
                    "Results from eSignUserManagement:getUserProfiles method:",
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
    public function worker($args): array
    {
        # Step 5a start
        $resultsArr = [];
        $results = new UsersDrilldownResponse();
        # Step 5a end

        $admin_api = $this->clientService->getUsersApi();

        # Here we set the from_date to filter envelopes for the last 10 days
        # Use ISO 8601 date format

        # Step 3 start
        $options = new GetUsersOptions();
        $options->setAccountId($args["account_id"]);
        $from_date = date("c", (time() - (10 * 24 * 60 * 60)));
        $options->setLastModifiedSince($from_date);

        $orgId = $this->clientService->getOrgAdminId($this->args);

        try {

            $modifiedUsers = $admin_api->getUsers($orgId, $options);
            # Step 3 end

            # Step 4 start
            foreach ($modifiedUsers["users"] as $user) {
                $profileOptions = new GetUserProfilesOptions();
                $profileOptions->setEmail($user["email"]);
                # Step 4 end

                # Step 5b start
                $res = $admin_api->getUserProfiles($orgId, $profileOptions);
                $results->setUsers($res->getUsers());
                $decoded = json_decode((string)$results, true);
                array_push($resultsArr, $decoded["users"]);
                # Step 5b end
            }

        } catch (ApiException $e) {
            $GLOBALS['twig']->display(
                'error.html',
                [
                    'error_code' => $e->getCode(),
                    'error_message' =>  $e->getMessage()
                ]
            );
            exit;
        }


        return  $resultsArr;
    }


    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];

        return $args;
    }
}
