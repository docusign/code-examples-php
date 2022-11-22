<?php

/**
 * Created by Visual Studio Code.
 * User: Aaron Jackson-Wilde
 * Date: 11/09/21
 * Time: 7:32 AM
 */

namespace Example\Controllers\Examples;

use Example\Services\ManifestService;
class SelectAPI
{
    public function controller()
    {


        /* 
        we don't know if the api_type has been set, 
        if it has never been set, this will trigger an 
        error in the manifest, therefore, we'll set it 
        to ESignature for the Manifest service to work. 
        */
        if(!isset($_SESSION['api_type'])){
            $_SESSION['api_type'] = "ESignature";
        }

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController();
        }
    }

    
    private function getController()
    {
        /* 
        we don't know if the api_type has been set, 
        if it has never been set, this will trigger an 
        error in the manifest, therefore, we'll set it 
        to ESignature for the Manifest service to work. 
        */
        if(!isset($_SESSION['api_type'])){
            $_SESSION['api_type'] = "ESignature";
        }

        $GLOBALS['twig']->display(
            'select_api.html',
            [
                'title' => 'Please select a DocuSign API',
                'show_doc' => false,
                'common_texts' => ManifestService::getCommonTexts()
            ]
        );
    }
}
