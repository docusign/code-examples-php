<?php

namespace Example\Services;

use DocuSign\Click\Api\AccountsApi;
use DocuSign\Click\Client\ApiClient;
use DocuSign\Click\Client\ApiException;
use DocuSign\Click\Configuration;
use Example\Controllers\BaseController;

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

        #ds-snippet-start:ClickPHPStep2
        $config = new Configuration();
        $config->setHost('https://demo.docusign.net/clickapi');
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $this->apiClient = new ApiClient($config);
        #ds-snippet-end:ClickPHPStep2

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
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=' . BaseController::LOGIN_REDIRECT);
        exit;
    }

    /**
     * Redirect user to the error page
     *
     * @param ApiException $e
     * @return void
     */
    public function showErrorTemplate(ApiException $e): void
    {

        if ($e->getCode() == 990) {
            $GLOBALS['twig']->display(
                'error.html',
                [
                'error_code' => ' ',
                'error_message' => $e->getMessage(),
                'common_texts' => ManifestService::getCommonTexts()
                ]
            );
        } else {
                $body = $e->getResponseBody();
                $GLOBALS['twig']->display(
                    'error.html',
                    [
                    'error_code' => $body->errorCode ?? unserialize($body)->errorCode,
                    'error_message' => $body->message ?? unserialize($body)->message,
                    'common_texts' => ManifestService::getCommonTexts()
                    ]
                );
        }
    }

     /**
     * Redirect user to results page
     *
     * @param $codeExampleText array
     * @param null $results
     * @param $message string
     * @return void
     */
    public function showDoneTemplateFromManifest(array $codeExampleText, $results = null, string $message = null): void
    {
        if ($message == null) {
            $message = $codeExampleText["ResultsPageText"];
        }

        $GLOBALS['twig']->display('example_done.html', [
            'title' => $codeExampleText["ExampleName"],
            'h1' => $codeExampleText["ExampleName"],
            'message' => $message,
            'json' => $results,
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
    public function showDoneTemplate(string $title, string $headline, string $message, $results = null): void
    {
        $GLOBALS['twig']->display(
            'example_done.html',
            [
                'title' => $title,
                'h1' => $headline,
                'message' => $message,
                'json' => $results,
                'common_texts' => ManifestService::getCommonTexts()
            ]
        );
        exit;
    }
}
