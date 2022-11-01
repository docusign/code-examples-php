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
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController();
        }
    }

    private function getController()
    {
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
