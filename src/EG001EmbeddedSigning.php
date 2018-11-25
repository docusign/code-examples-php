<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:32 AM
 */

namespace Example;


class EG001EmbeddedSigning
{

    public function controller()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET' ) {$this->getController();};
        if ($method == 'POST') {$this->createController();};
    }

    private function getController()
    {
        $GLOBALS['twig']->display('eg001_embedded_signing.html', [
            'show_doc' => false,
            'source_url' => 'http://xerox.com',
            'source_file' => 'Xerox!',
            'signer_email' => 'larry@kluger.com',
            'signer_name' => 'Larry Kluger']);


    }

    private function createController()
    {
        $GLOBALS['twig']->display('eg001_embedded_signing.html', [
            'show_doc' => false,
            'source_url' => 'http://xerox.com',
            'source_file' => 'Xerox!',
            'signer_email' => 'larry@kluger.com',
            'signer_name' => 'Larry Kluger']);


    }

}