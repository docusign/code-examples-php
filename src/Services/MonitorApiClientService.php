<?php


namespace Example\Services;

use DocuSign\Monitor\Client\ApiClient;
use DocuSign\Monitor\Client\ApiException;
use DocuSign\Monitor\Configuration;

class MonitorApiClientService
{
    /**
     * DocuSign API Client
     */
    public $apiClient;

    /**
     * Router Service
     */
    public $routerService;

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
        # step 2 start
        $config = new Configuration();
        $accessToken = $args['ds_access_token'];

        $config->setAccessToken($accessToken);
        $config->setHost('https://lens-d.docusign.net');
        $config->addDefaultHeader('Authorization', 'Bearer ' . $accessToken);
        $config->addDefaultHeader("Content-Type", "application/json");       
        $this->apiClient = new ApiClient($config);
        # step 2 end 
        
        $this->routerService = new RouterService();
    }

    public function getApiClient(){
        return $this->apiClient;
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

        print_r($e);
        $GLOBALS['twig']->display('error.html', [
                'error_code' => $body->errorCode ?? unserialize($body)->errorCode,
                'error_message' => $body->message ?? unserialize($body)->message]
        );
    }

    /**
     * Redirect user to the error page
     *
     * @param $title string
     * @param $headline string
     * @param $message string
     * @param $results
     * @return void
     */
    public function showDoneTemplate($title, $headline, $message, $results = null): void
    {
        $GLOBALS['twig']->display('example_done.html', [
            'title' => $title,
            'h1' => $headline,
            'message' => $message,
            'json' => $results
        ]);
        exit;
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
}
