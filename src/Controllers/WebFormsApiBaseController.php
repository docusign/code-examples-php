<?php

namespace DocuSign\Controllers;

use DocuSign\Services\ApiTypes;
use DocuSign\Services\ManifestService;
use DocuSign\Services\RouterService;
use DocuSign\Services\SignatureClientService;
use DocuSign\Services\WebFormsApiClientService;

abstract class WebFormsApiBaseController extends BaseController
{
    private const MINIMUM_BUFFER_MIN = 3;
    protected WebFormsApiClientService $clientService;
    protected SignatureClientService $eSignatureClientService;
    protected RouterService $routerService;
    protected array $args;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new WebFormsApiClientService($this->args);
        $this->eSignatureClientService = new SignatureClientService($this->args);

        $this->routerService = new RouterService();
        $this->codeExampleText = $this->getPageText(static::EG);

        if (defined("static::EG")) {
            $this->checkDsToken();
        }
    }

    abstract protected function getTemplateArgs(): array;

    /**
     * Check ds
     */
    protected function checkDsToken(): void
    {
        $currentAPI = ManifestService::getAPIByLink(static::EG);

        if (!$this->routerService->dsTokenOk(self::MINIMUM_BUFFER_MIN)
            || $currentAPI !== $_SESSION['api_type']
        ) {
            $_SESSION['prefered_api_type'] = ApiTypes::WEBFORMS;
            $this->clientService->needToReAuth(static::EG);
        }
    }

    /**
     * Base controller
     *
     * @param $args array|null
     * @return void
     */
    public function controller(array $args = null): void
    {
        $this->codeExampleText = $this->getPageText(static::EG);

        if ($this->isMethodGet()) {
            $this->getController($this->routerService, basename(static::FILE), $args);
        }
        if ($this->isMethodPost()) {
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
        ?string       $basename,
        ?array        $args
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
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url']
                        . "/WebForm/" . $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation']
                        . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'args' => $args,
                    'code_example_text' => $this->codeExampleText,
                    'common_texts' => $this->getCommonText()
                ]);
            } else {
                # Save the current operation so it will be resumed after authentication
                $_SESSION['prefered_api_type'] = ApiTypes::WEBFORMS;
                $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . static::EG;
                header(
                    'Location: ' . $GLOBALS['app_url']
                    . 'index.php?page=' . static::LOGIN_REDIRECT
                );
                exit;
            }
        }
    }

    /**
     * Declaration for the base controller creator.
     * Each creator should be described in specific Controller
     */
    abstract protected function createController(): void;

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
