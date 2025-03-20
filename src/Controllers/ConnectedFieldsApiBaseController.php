<?php

namespace DocuSign\Controllers;

use DocuSign\Services\SignatureClientService;
use DocuSign\Services\RouterService;
use DocuSign\Services\IRouterService;
use DocuSign\Services\ApiTypes;
use DocuSign\Services\ManifestService;

abstract class ConnectedFieldsApiBaseController extends BaseController
{
    private const MINIMUM_BUFFER_MIN = 3;
    protected SignatureClientService $clientService;
    protected IRouterService $routerService;
    protected array $args;

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        if (defined("static::EG")) {
            $this->checkDsToken();
        }
    }

    abstract protected function getTemplateArgs(): array;

    /**
     * Base controller
     *
     * @param null $eg
     * @return void
     */
    public function controller($args = null): void
    {
        if (empty($eg)) {
            $eg = static::EG;
            $this->codeExampleText = $this->getPageText(static::EG);
        }

        $this->codeExampleText = $this->getPageText(static::EG);
        
        if ($this->isMethodGet()) {
            $this->getController(
                $args,
                basename(static::FILE)
            );
        }

        if ($this->isMethodPost()) {
            $this->createController();
        }
    }

    /**
     * Show the example's form page
     *
     * @param $eg
     * @param $basename string|null
     * @param $brand_languages array|null
     * @param $brands array|null
     * @param $permission_profiles array|null
     * @param $groups array|null
     * @return void
     */
    protected function getController(
        $args,
        ?string $basename
    ): void {
        if ($this->isHomePage(static::EG)) {
            $GLOBALS['twig']->display(static::EG . '.html', [
                'title' => $this->homePageTitle(static::EG),
                'show_doc' => false,
                'common_texts' => $this->getCommonText()
            ]);
        } else {
            $currentAPI = ManifestService::getAPIByLink(static::EG);
            if ($this->routerService->dsTokenOk() && $currentAPI === $_SESSION['api_type']) {
                $displayOptions = [
                    'title' => $this->routerService->getTitle(static::EG),
                    'source_file' => $basename,
                    'args' => $args,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . "/ConnectedFields/".  $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . static::EG,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                    'signer_email' => $GLOBALS['DS_CONFIG']['signer_email'],
                    'code_example_text' => $this->codeExampleText,
                    'common_texts' => $this->getCommonText()
                ];

                $GLOBALS['twig']->display($this->routerService->getTemplate(static::EG), $displayOptions);
            } else {
                $_SESSION['prefered_api_type'] = ApiTypes::CONNECTEDFIELDS;
                $this->saveCurrentUrlToSession(static::EG);
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
     * Check email input value using regular expression
     * @param $email
     * @return string
     */
    protected function checkEmailInputValue($email): string
    {
        return preg_replace('/([^\w +\-\@\.\,])+/', '', $email);
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
        $currentAPI = ManifestService::getAPIByLink(static::EG);
        
        if (!$this->routerService->dsTokenOk(self::MINIMUM_BUFFER_MIN) || $currentAPI !== $_SESSION['api_type']) {
            $_SESSION['prefered_api_type'] = ApiTypes::CONNECTEDFIELDS;
            $this->clientService->needToReAuth(static::EG);
        }
    }
}
