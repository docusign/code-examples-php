<?php

namespace Example\Controllers;

use Example\Services\AdminApiClientService;
use Example\Services\RouterService;

abstract class AdminBaseController extends BaseController
{
    private const MINIMUM_BUFFER_MIN = 3;
    protected AdminApiClientService $clientService;
    protected RouterService $routerService;
    protected array $args;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new AdminApiClientService($this->args);
        $this->routerService = new RouterService();
    }

    abstract function getTemplateArgs(): array;

    public function controller()
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
        $exportId = isset($_SESSION['export_id']);
        $importId = isset($_SESSION['import_id']);
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
                $GLOBALS['twig']->display(
                    $routerService->getTemplate(static::EG),
                    [
                        'title' => $routerService->getTitle(static::EG),
                        'source_file' => $basename,
                        'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                        'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                        'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                        'args' => $args,
                        'export_id' => $exportId,
                        'import_id' => $importId
                    ]
                );
            } else {
                # Save the current operation so it will be resumed after authentication
                $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . static::EG;
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
                exit;
            }
        }
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController();

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
     * Check input values using regular expressions
     * @param $value
     * @return string
     */
    protected function checkInputValues($value): string
    {
        return preg_replace('/([^\w \-\@\.\,])+/', '', $value);
    }

    /**
     * Check email input value using regular expression
     * @param $email
     * @return string
     */
    protected function checkEmailInputValue($email): string
    {
        return preg_replace('/([^\w +\-\@\.\,])+/', '', $email);
    }
}
