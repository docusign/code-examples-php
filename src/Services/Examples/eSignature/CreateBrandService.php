<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Brand;
use DocuSign\Services\ManifestService;

class CreateBrandService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @return array ['redirect_url']
     */
    public static function createBrand(array $args, $clientService): array
    {
        # Step 3. Construct the request body
        #ds-snippet-start:eSign28Step3
        $accounts_api = $clientService->getAccountsApi();
        $brand = new Brand([
            'brand_name' => $args['brand_args']['brand_name'],
            'default_brand_language' => $args['brand_args']['default_language']
        ]);
        #ds-snippet-end:eSign28Step3

        try {
            # Step 4 Call the eSignature REST API
            #ds-snippet-start:eSign28Step4
            $createdBrand = $accounts_api->createBrand($args['account_id'], $brand);
            #ds-snippet-end:eSign28Step4
        } catch (ApiException $e) {
            $error_code = $e->getResponseBody()->errorCode;
            $error_message = $e->getResponseBody()->message;
            if ($error_message == "Invalid brand name. Duplicate brand names are not allowed.") {
                return ['brand_id' => null];
            } else {
                $GLOBALS['twig']->display('error.html', [
                        'error_code' => $error_code,
                        'error_message' => $error_message,
                        'common_texts' => ManifestService::getCommonTexts()
                    ]);
                exit;
            }
        }

        return ['brand_id' => $createdBrand->getBrands()[0]->getBrandId()];
    }
}
