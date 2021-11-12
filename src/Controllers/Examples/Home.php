<?php

/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace Example\Controllers\Examples;

use Example\Controllers\eSignBaseController;
use Example\Services\RouterService;

class Home extends eSignBaseController
{
    /** RouterService */
    protected RouterService $routerService;
    const FILE = __FILE__;
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
        // this is never getting fired off, even when POST is set
        // if (isset($_POST["api_type"])){
        //     $_SESSION['api_type'] = $_POST['api_type'];
        // }

        if($_SESSION['api_type'] == 'Rooms'){
            $this->eg = 'home_rooms';
        } elseif($_SESSION['api_type'] == 'Click') {
            $this->eg = 'home_click';
        } elseif($_SESSION['api_type'] == 'Monitor') {
            $this->eg = 'home_monitor';
        } elseif($_SESSION['api_type'] == 'Admin') {
            $this->eg = 'home_admin';
        } else {
            $this->eg = 'home_esig';
        }
    
        if (empty($this->routerService)) {
            $this->routerService = new RouterService();
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
