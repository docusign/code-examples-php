<?php

namespace Example\Controllers;

use Example\Services\AdminApiClientService;
use Example\Services\RouterService;

abstract class AdminBaseController extends BaseController
{

    protected array $args;
    protected RouterService $routerService;
    protected AdminApiClientService $clientService;
    private const MINIMUM_BUFFER_MIN = 3;
    protected string $organizationId;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
        $this->organizationId = $GLOBALS['DS_CONFIG']['organization_id'];
    }

    /**
     * Admin base controller
     * @return void
     */
    public function controller(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if (!$this->routerService) {
            $this->routerService = new RouterService();
        }

        if ($method == 'GET') {
            $this->getController();
        }

        if ($method == 'POST') {
            $this->routerService->check_csrf();
            $this->createController();
        }
    }

    /**
     * Show the example's form page
     * @return void
     */
    private function getController(
    ): void
    {
        if ($this->isHomePage(static::EG)) {
            $GLOBALS['twig']->display(static::EG . '.html', [
                'title' => $this->homePageTitle(static::EG),
                'show_doc' => false
            ]);
        } else {
            if ($this->routerService->ds_token_ok()) {
                $GLOBALS['twig']->display($this->routerService->getTemplate(static::EG), [
                    'title' => $this->routerService->getTitle(static::EG),
                    'source_file' => basename(static::FILE),
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . basename(static::FILE),
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'args' => $this->args,
                    'export_id' => isset($_SESSION['export_id']),
                    'import_id' => isset($_SESSION['import_id'])
                ]);
            } else {
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
     * Declaration for the template arguments preparation. Each creator should be described in specific Controller
     */
    abstract function getTemplateArgs(): array;
}
