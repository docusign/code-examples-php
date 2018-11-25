<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace Example;


class Home
{

    public function controller()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {$this->getController();};
    }

    private function getController()
    {
        $GLOBALS['twig']->display('must_authenticate.html', [
            'title' => 'You must authenticate',
            'show_doc' => false
        ]);
    }

}