<?php

namespace Example\Controllers\Examples;

use Example\Services\ManifestService;

class MustAuthenticate
{
    public function controller()
    {
        $GLOBALS['twig']->display(
            'must_authenticate.html',
            [
                'title' => 'Please authenticate with DocuSign',
                'show_doc' => false,
                'common_texts' => ManifestService::getCommonTexts()
            ]
        );
    }
}
