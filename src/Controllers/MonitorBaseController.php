<?php

namespace DocuSign\Controllers;

use DocuSign\Services\MonitorApiClientService;
use DocuSign\Services\RouterService;
use DocuSign\Services\ApiTypes;
use DocuSign\Services\ManifestService;

abstract class MonitorBaseController extends BaseController
{
    protected MonitorApiClientService $clientService;
    protected RouterService $routerService;
    protected array $args;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new MonitorApiClientService($this->args);
        $this->routerService = new RouterService();
    }

    abstract protected function getTemplateArgs(): array;

    /**
     * Monitor base controller
     *
     * @return void
     */
    public function controller(): void
    {
        $this->codeExampleText = $this->getPageText(static::EG);

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($this->routerService, basename(static::FILE), $this->args);
        }
        if ($method == 'POST') {
            $this->routerService->checkCsrf();
            $this->createController();
        }
    }

    /**
     * Show the example's form page
     *
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $args array|null
     * @return void
     */
    private function getController(
        RouterService $routerService,
        ?string $basename,
        ?array $args
    ): void {
        if ($this->isHomePage(static::EG)) {
            $GLOBALS['twig']->display(
                static::EG . '.html',
                [
                    'title' => $this->homePageTitle(static::EG),
                    'show_doc' => false,
                    'common_texts' => $this->getCommonText()
                ]
            );
        } else {
            $currentAPI = ManifestService::getAPIByLink(static::EG);

            if ($routerService->dsTokenOk() && $currentAPI === $_SESSION['api_type']) {
                $GLOBALS['twig']->display($routerService->getTemplate(static::EG), [
                    'title' => $routerService->getTitle(static::EG),
                    'source_file' => $basename,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url']  . "/Monitor/".  $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'args' => $args,
                    'code_example_text' => $this->codeExampleText,
                    'common_texts' => $this->getCommonText()
                ]);
            } else {
                # Save the current operation so it will be resumed after authentication
                $_SESSION['prefered_api_type'] = ApiTypes::MONITOR;
                $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . static::EG;
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=' . static::LOGIN_REDIRECT);
                exit;
            }
        }
    }
    
    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */

    abstract protected function createController(): void;

    /**
     * Check input values using regular expressions
     * @param $value
     * @return string
     */
    protected function checkInputValues($value): string
    {
        return preg_replace('/([^\w \-\@\.\,])+/', '', $value);
    }

    /**
     * Provides the default template arguments
     * @return array
     */
    protected function getDefaultTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }
}
