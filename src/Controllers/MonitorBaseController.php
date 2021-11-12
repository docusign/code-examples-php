<?php

namespace Example\Controllers;

use Example\Services\MonitorApiClientService;
use Example\Services\RouterService;

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

    abstract function getTemplateArgs(): array;

    /**
     * Monitor base controller
     *
     * @return void
     */
    public function controller(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($this->routerService, basename(static::FILE), $this->args);
        }
        if ($method == 'POST') {
            $this->routerService->check_csrf();
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
                    'show_doc' => false
                ]
            );
        } else {
            if ($routerService->ds_token_ok()) {
                $GLOBALS['twig']->display($routerService->getTemplate(static::EG), [
                    'title' => $routerService->getTitle(static::EG),
                    'source_file' => $basename,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url']  . "/Monitor/".  $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'args' => $args,
                ]);
            }
            else {
                # Save the current operation so it will be resumed after authentication
                $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . static::EG;
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=select_api');
                exit;
            }
        }
    }
    
    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */

    abstract function createController(): void;
    
    /**
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
