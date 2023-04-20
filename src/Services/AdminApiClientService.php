<?php

namespace Example\Services;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Api\AccountsApi;
use DocuSign\Admin\Api\BulkExportsApi;
use DocuSign\Admin\Api\BulkImportsApi;
use DocuSign\Admin\Api\DSGroupsApi;
use DocuSign\Admin\Api\ProductPermissionProfilesApi;
use DocuSign\Admin\Client\ApiClient;
use DocuSign\Admin\Api\UsersApi;
use DocuSign\Admin\Configuration;
use Example\Controllers\BaseController;
use DocuSign\Admin\Api\UsersApi\GetUserProfilesOptions;

use Stringable;

class AdminApiClientService
{
    /**
     * DocuSign API Client
     */
    public ApiClient $apiClient;

    /**
     * Router Service
     */
    public RouterService $routerService;

    /**
     * Create a new controller instance.
     *
     * @param $args
     * @return void
     */
    public function __construct($args)
    {
        # Construct your API headers
        # Exceptions will be caught by the calling function
        $accessToken = $args['ds_access_token'];

        # step 2 start
        $config = new Configuration();
        $config->setAccessToken($accessToken);
        $config->setHost('https://api-d.docusign.net/management');
        $config->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);  
        $this->apiClient = new ApiClient($config);
        # step 2 end 
        
        $this->routerService = new RouterService();
    }

    /**
     * Getter for the AccountsApi
     */
    public function getAccountsApi(): AccountsApi
    {
        return new AccountsApi($this->apiClient);
    }

    /**
     * Getter for the UsersAPI
     */
    public function getUsersApi(): UsersApi
    {
        return new UsersApi($this->apiClient);
    }
    
    /**
    * Get product permission profiles
    */
    public function permProfilesApi(): ProductPermissionProfilesApi
    {
        return new ProductPermissionProfilesApi($this->apiClient);
    }
    
    /**
    * Get Org Admin Id
    *
    */
    public function getOrgAdminId(): String
    { 
       // It is possible for an account to belong to multiple organizations 
       // We are returning the first Organization Id found
       $AccountsApi = new AccountsApi($this->apiClient);
       $orgs = $AccountsApi->getOrganizations();
       if ($orgs["organizations"] == null)
         throw new ApiException ("You must create an organization for this account to use the DocuSign Admin API. For details, see <a target='_blank' href='https://support.docusign.com/guides/org-admin-guide'> this support article.</a>", 1);
       else
         return $orgs["organizations"][0]["id"];
        
    }
    
    /**
    * Get product Admin Groups
    */
    public function adminGroupsApi(): DSGroupsApi
    {
        return new DSGroupsApi($this->apiClient);
    }
    
    /**
     * Get Bulk Exports API
     */

    public function bulkExportsAPI(): BulkExportsApi
    {
        return new BulkExportsApi($this->apiClient);
    }
     
    /**
     * Get Bulk Imports API
     */

    public function bulkImportsApi(): BulkImportsApi
    {
        return new BulkImportsApi($this->apiClient);
    }

    /**
     * Redirect user to the auth page
     *
     * @param $eg
     * @return void
     */
    public function needToReAuth($eg): void
    {
        $this->routerService->flash('Sorry, you need to re-authenticate.');
        # We could store the parameters of the requested operation
        # so it could be restarted automatically.
        # But since it should be rare to have a token issue here,
        # we'll make the user re-enter the form data after
        # authentication.
        $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=' . BaseController::LOGIN_REDIRECT);
        exit;
    }

    /**
     * Redirect user to the error page
     *
     * @param  ApiException $e
     * @return void
     */
    public function showErrorTemplate(ApiException $e): void
    {
        $GLOBALS['twig']->display('error.html', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'common_texts' => ManifestService::getCommonTexts()
            ]
        );
    }

     /**
     * Redirect user to results page
     *
     * @param $codeExampleText array
     * @param null $results
     * @param $message string
     * @return void
     */
    public function showDoneTemplateFromManifest(array $codeExampleText, $results = null, string $import_id = null, string $message = null): void
    {
        if ($message == null) {
            $message = $codeExampleText["ResultsPageText"];
        }

        $GLOBALS['twig']->display('example_done.html', [
            'title' => $codeExampleText["ExampleName"],
            'h1' => $codeExampleText["ExampleName"],
            'message' => $message,
            'json' => $results,
            'import_id' => $import_id,
            'common_texts' => ManifestService::getCommonTexts()
        ]);
        exit;
    }

    /**
     * Redirect user to results page
     *
     * @param $title string
     * @param $headline string
     * @param $message string
     * @param null $results
     * @return void
     */
    public function showDoneTemplate(string $title, string $headline, string $message, $results = null, string $import_id = null): void
    {
        $GLOBALS['twig']->display('example_done.html', [
            'title' => $title,
            'h1' => $headline,
            'message' => $message,
            'json' => $results,
            'import_id' => $import_id,
            'common_texts' => ManifestService::getCommonTexts()
        ]);
        exit;
    }
}
