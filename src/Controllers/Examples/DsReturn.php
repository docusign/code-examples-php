<?php

/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace DocuSign\Controllers\Examples;

use DocuSign\Controllers\BaseController;
use DocuSign\Services\RouterService;

class DsReturn extends BaseController
{
    private RouterService $routerService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->routerService = new RouterService();
        $this->getController();
    }

    /**
     * Get the base controller for return action
     *
     * @return void
     */
    public function getController(): void
    {
    }

    public function createController(): void
    {
    }
}
