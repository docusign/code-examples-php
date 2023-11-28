<?php

namespace DocuSign\Controllers;

use DocuSign\Services\RouterService;
use DocuSign\Services\ApiTypes;
use DocuSign\Services\ManifestService;
use DocuSign\Services\ConnectApiClientService;

abstract class ConnectBaseController extends BaseController
{
    protected RouterService $routerService;
    protected ConnectApiClientService $clientService;
    protected array $args;
    private const MINIMUM_BUFFER_MIN = 3;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->routerService = new RouterService();
        $this->clientService = new ConnectApiClientService($this->args);
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

        if ($this->isMethodGet()) {
            $_SESSION['prefered_api_type'] = ApiTypes::CONNECT;
            $this->getController($this->routerService, basename(static::FILE), $this->args);
        }
        if ($this->isMethodPost()) {
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
        string $basename,
        array $args
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
            $GLOBALS['twig']->display($routerService->getTemplate(static::EG), [
                'title' => $routerService->getTitle(static::EG),
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url']  . '/Connect/'.  $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                'args' => $args,
                'code_example_text' => $this->codeExampleText,
                'common_texts' => $this->getCommonText()
            ]);
        }
    }
    
    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract protected function createController(): void;

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
