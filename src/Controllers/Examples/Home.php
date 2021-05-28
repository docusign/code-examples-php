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
    private RouterService $routerService;

    private string $eg;  # Reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if($GLOBALS['EXAMPLES_API_TYPE']['Rooms'] == true){
            $this->eg = 'home_rooms';
        } elseif($GLOBALS['EXAMPLES_API_TYPE']['Click'] == true) {
            $this->eg = 'home_click';
        } elseif($GLOBALS['EXAMPLES_API_TYPE']['Monitor'] == true) {
            $this->eg = 'home_monitor';
        } else {
            $this->eg = 'home';
        }
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService);
    }

    public function createController() {}
}