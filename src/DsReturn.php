<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace Example;


class DsReturn
{

    public function controller()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {$this->getController();};
    }

    private function getController()
    {
        $GLOBALS['twig']->display('ds_return.html', [
                  'title' => 'Returned data',
                  'event' => isset($_GET['event'      ]) ? $_GET['event'      ] : false,
            'envelope_id' => isset($_GET['envelope_id']) ? $_GET['envelope_id'] : false,
                  'state' => isset($_GET['state'      ]) ? $_GET['state'      ] : false
        ]);
    }
}
