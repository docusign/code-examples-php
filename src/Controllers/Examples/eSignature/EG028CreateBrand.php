<?php

namespace Example\Controllers\Examples\eSignature;

use Example\Controllers\eSignBaseController;
use Example\Services\Examples\eSignature\CreateBrandService;

class EG028CreateBrand extends eSignBaseController
{
    const EG = 'eg028'; # reference (and URL) for this example
    const FILE = __FILE__;

    private array $brand_languages = [ # Default languages for brand
        "Arabic" => "ar",
        "Armenian" => "hy",
        "Bahasa Indonesia" => "id",
        "Bahasa Malay" => "ms",
        "Bulgarian" => "bg",
        "Chinese Simplified" => "zh_CN",
        "Chinese Traditional" => "zh_TW",
        "Croatian" => "hr",
        "Czech" => "cs",
        "Danish" => "da",
        "Dutch" => "nl",
        "English UK" => "en_GB",
        "English US" => "en",
        "Estonian" => "et",
        "Farsi" => "fa",
        "Finnish" => "fi",
        "French" => "fr",
        "French Canada" => "fr_CA",
        "German" => "de",
        "Greek" => "el",
        "Hebrew" => "he",
        "Hindi" => "hi",
        "Hungarian" => "hu",
        "Italian" => "it",
        "Japanese" => "ja",
        "Korean" => "ko",
        "Latvian" => "lv",
        "Lithuanian" => "lt",
        "Norwegian" => "no",
        "Polish" => "pl",
        "Portuguese" => "pt",
        "Portuguese Brasil" => "pt_BR",
        "Romanian" => "ro",
        "Russian" => "ru",
        "Serbian" => "sr",
        "Slovak" => "sk",
        "Slovenian" => "sl",
        "Spanish" => "es",
        "Spanish Latin America" => "es_MX",
        "Swedish" => "sv",
        "Thai" => "th",
        "Turkish" => "tr",
        "Ukrainian" => "uk",
        "Vietnamese" => "vi"
    ];

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        parent::controller(null, $this->brand_languages);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        # 1. Call the worker method
        # More data validation would be a good idea here
        # Strip anything other than characters listed
        $brandId = CreateBrandService::createBrand($this->args, $this->clientService);

        if ($brandId["brand_id"] != null) {
            # Success if there's an envelope Id and the brand name isn't a duplicate
            $this->clientService->showDoneTemplate(
                "Create a brand",
                "Create a brand",
                "The Brand has been created!<br/> Brand ID {$brandId["brand_id"]}"
            );
        }
        # If the brand name is null the brand name is a duplicate.
        else {
            $GLOBALS['twig']->display('error_eg028.html', [
                'title' => 'Duplicate Brand Name'
            ]);
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        $brand_args = [
            'brand_name' => $this->checkInputValues($_POST['brand_name']),
            'default_language' => $this->checkInputValues($_POST['default_language']),
        ];
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'brand_args' => $brand_args
        ];
    }
}
