<?php

/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace Example\Controllers\Examples;

class MustAuthenticate
{
    public function controller()
    {
        $GLOBALS['twig']->display(
            'must_authenticate.html',
            [
                'title' => 'Please authenticate with DocuSign',
                'show_doc' => false
            ]
        );
    }
}
