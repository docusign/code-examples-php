<?php

/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace DocuSign\Controllers\Examples;

use QuickACG\RouterService as QuickRouterService;
use DocuSign\Controllers\eSignBaseController;
use DocuSign\Services\RouterService;
use DocuSign\Services\IRouterService;

class Home extends eSignBaseController
{
    const FILE = __FILE__;
    /** RouterService */
    protected IRouterService $routerService;
    private string $eg;  # Reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        session_start();

        $this->eg = 'home_esig';

        if (empty($this->routerService)) {
            $this->routerService = $GLOBALS['DS_CONFIG']['quickACG'] === "true" ? new QuickRouterService(): new RouterService();
        }
        parent::controller($this->eg);
    }

    public function createController(): void
    {
    }

    public function getTemplateArgs(): array
    {
        return [];
    }
}
