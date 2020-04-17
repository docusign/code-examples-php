<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace Example\Controllers\Templates;

use Example\Controllers\BaseController;
use Example\Services\RouterService;

class DsReturn extends BaseController
{
    /** RouterService */
    private $routerService;

    private $eg = "ds_return";  # Reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService);
    }

    /**
     * Get the base controller for return action
     *
     * @return void
     */
    public function getController()
    {
        $GLOBALS['twig']->display('ds_return.html', [
            'title' => 'Returned data',
            'event' => isset($_GET['event']) ? $_GET['event'] : false,
            'envelope_id' => isset($_GET['envelope_id']) ? $_GET['envelope_id'] : false,
            'state' => isset($_GET['state']) ? $_GET['state'] : false
        ]);
    }
}
