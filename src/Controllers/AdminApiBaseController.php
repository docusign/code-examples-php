<?php

namespace Example\Controllers;

use Example\Services\AdminApiClientService;
use Example\Services\RouterService;

abstract class AdminApiBaseController extends BaseController
{
    protected array $args;
    protected RouterService $routerService;
    protected AdminApiClientService $clientService;
    private const MINIMUM_BUFFER_MIN = 3;


    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
    }
    /**
     * Base controller
     *
     * @param $args array|null
     * @return void
     */
    public function controller(
        $args = null,
        $permission_profiles = null
    ): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (!$this->routerService) {
            $this->routerService = new RouterService();
        }

        if ($method == 'GET') {
            $this->getController($args, $permission_profiles);
        };
        if ($method == 'POST') {
            $this->routerService->check_csrf();
            $this->createController();
        };
    }

    /**
     * Show the example's form page
     *
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $args array|null
     * @return void
     */
    private function getController(
        ?array $args,
        ?array $permission_profiles
    ): void
    {
        if ($this->isHomePage(static::EG)) {
            $GLOBALS['twig']->display(static::EG . '.html', [
                'title' => $this->homePageTitle(static::EG),
                'show_doc' => false
            ]);
        }
        else
        {
            if ($this->routerService->ds_token_ok()) {
                $GLOBALS['twig']->display($this->routerService->getTemplate(static::EG), [
                    'title' => $this->routerService->getTitle(static::EG),
                    'source_file' => basename(static::FILE),
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . "/Admin/". basename(static::FILE),
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'args' => $args,
                    'permission_profiles' => $permission_profiles,
                    'export_id' => isset($_SESSION['export_id']),
                    'import_id' => isset($_SESSION['import_id'])
                ]);
            }
            else {
                # Save the current operation so it will be resumed after authentication
                $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . static::EG;
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
                exit;
            }
        }
    }

    /**
     * Check ds
     */
    protected function checkDsToken()
    {
        if (!$this->routerService->ds_token_ok(self::MINIMUM_BUFFER_MIN)) {
            $this->clientService->needToReAuth(static::EG);
        }
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController();

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

    abstract function getTemplateArgs(): array;
}
