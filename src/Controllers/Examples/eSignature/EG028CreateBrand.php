<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Brand;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG028CreateBrand extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg028";       # Reference (and URL) for this example

    private $brand_languages = [ # Default languages for brand    
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
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__), $this->brand_languages);
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 1. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results["brand_id"] != null) {
                # Success if there's an envelope Id and the brand name isn't a duplicate
                $this->clientService->showDoneTemplate(
                    "New Brand sent",
                    "New Brand sent",
                    "The Brand has been created!<br/> Brand ID {$results["brand_id"]}."
                );
            }
            # If the brand name is null the brand name is a duplicate.
            else {
                $GLOBALS['twig']->display('error_eg028.html', [
                    'title' => 'Duplicate Brand Name'
                ]);
            }
        } 
        else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    # ***DS.snippet.0.start
    public function worker($args): array
    {
        # Step 3. Construct the request body
        $accounts_api = $this->clientService->getAccountsApi();
        $brand = new Brand([
            'brand_name' => $args['brand_args']['brand_name'],
            'default_brand_language' => $args['brand_args']['default_language']
        ]);

        try {
            # Step 4 Call the eSignature REST API
            $results = $accounts_api->createBrand($args['account_id'], $brand);
        } catch (ApiException $e) {
            $error_code = $e->getResponseBody()->errorCode;
            $error_message = $e->getResponseBody()->message;
            if ($error_message == "Invalid brand name. Duplicate brand names are not allowed.") {
                return ['brand_id' => null];
            }
            else {
                $GLOBALS['twig']->display('error.html', [
                        'error_code' => $error_code,
                        'error_message' => $error_message]
                );
                exit;
            }              
        }

        return ['brand_id' => $results->getBrands()[0]->getBrandId()];
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $brand_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['brand_name']);
        $default_language = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['default_language']);
        $brand_args = [
            'brand_name' => $brand_name,
            'default_language' => $default_language,
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'brand_args' => $brand_args
        ];

        return $args;
    }
}