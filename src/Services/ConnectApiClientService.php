<?php

namespace DocuSign\Services;

use DocuSign\Controllers\BaseController;

class ConnectApiClientService
{
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
        $this->routerService = new RouterService();
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
            $message = $codeExampleText['ResultsPageText'];
        }

        $GLOBALS['twig']->display('example_done.html', [
            'title' => $codeExampleText['ExampleName'],
            'h1' => $codeExampleText['ExampleName'],
            'message' => $message,
            'json' => $results,
            'common_texts' => ManifestService::getCommonTexts()
        ]);
        exit;
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

    /**
     * Redirect user to the auth page
     *
     * @param $eg
     * @return void
     */
    public function needToReAuth(string $eg): void
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
}
