<?php

namespace Example\Services;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Api\AccountsApi;
use DocuSign\Click\Client\ApiClient;
use DocuSign\Click\Configuration;

class ClickApiClientService
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
        
        # Step 2 Start
        $config = new Configuration();
        $config->setHost('https://demo.docusign.net/clickapi');
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $this->apiClient = new ApiClient($config);
        # Step 2 End
        
        $this->routerService = new RouterService();
    }

    /**
     * Getter for the AccountsApi
     */
    public function accountsApi(): AccountsApi
    {
        return new AccountsApi($this->apiClient);
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
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
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
        $body = $e->getResponseBody();
        echo json_encode($body);
        $GLOBALS['twig']->display('error.html', [
                'error_code' => $body->errorCode ?? unserialize($body)->errorCode,
                'error_message' => $body->message ?? unserialize($body)->message]
        );
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
    public function showDoneTemplate(string $title, string $headline, string $message, $results = null): void
    {
        $GLOBALS['twig']->display('example_done.html', [
            'title' => $title,
            'h1' => $headline,
            'message' => $message,
            'json' => $results
        ]);
        exit;
    }
}
