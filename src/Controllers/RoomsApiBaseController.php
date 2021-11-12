<?php

namespace Example\Controllers;

use Example\Services\RoomsApiClientService;
use Example\Services\RouterService;

abstract class RoomsApiBaseController extends BaseController
{
    private const MINIMUM_BUFFER_MIN = 3;
    protected RoomsApiClientService $clientService;
    protected RouterService $routerService;
    protected array $args;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new RoomsApiClientService($this->args);
        $this->routerService = new RouterService();
    }

    abstract function getTemplateArgs(): array;

    /**
     * Base controller
     *
     * @param $templates array|null
     * @param $rooms array|null
     * @param $forms array|null
     * @param null $offices
     * @param null $formGroups
     * @return void
     */
    public function controller(
        array $templates = null,
        array $rooms = null,
        array $forms = null,
        $offices = null,
        $formGroups = null
    ): void {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController(basename(static::FILE), $templates, $rooms, $forms, $offices, $formGroups);
        }
        if ($method == 'POST') {
            $this->routerService->check_csrf();
            $this->createController();
        }
    }

    /**
     * Show the example's form page
     *
     * @param $basename string|null
     * @param $templates array|null
     * @param $rooms array|null
     * @param $forms array|null
     * @param null $offices
     * @param null $formGroups
     * @return void
     */
    private function getController(
        ?string $basename,
        ?array $templates,
        array $rooms = null,
        array $forms = null,
        $offices = null,
        $formGroups = null
    ): void
    {
        if ($this->isHomePage(static::EG)){
            $GLOBALS['twig']->display(static::EG . '.html', [
                'title' => $this->homePageTitle(static::EG),
                'show_doc' => false
            ]);
       
         } else {

            if ($this->routerService->ds_token_ok()) {
                $GLOBALS['twig']->display($this->routerService->getTemplate(static::EG), [
                    'title' => $this->routerService->getTitle(static::EG),
                    'templates' => $templates,
                    'rooms' => $rooms,
                    'forms' => $forms,
                    'offices' => $offices,
                    'form_groups' => $formGroups,
                    'source_file' => $basename,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . "/Rooms/". $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
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
     * Check ds
     */
    protected function checkDsToken(): void
    {
        if (!$this->routerService->ds_token_ok(self::MINIMUM_BUFFER_MIN)) {
            $this->clientService->needToReAuth(static::EG);
        }
    }
}
