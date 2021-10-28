<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Brand;

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
    # ***DS.snippet.0.start
    public static function createBrand(array $args, $clientService): array
    {
        # Step 3. Construct the request body
        $accounts_api = $clientService->getAccountsApi();
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
            } else {
                $GLOBALS['twig']->display('error.html', [
                        'error_code' => $error_code,
                        'error_message' => $error_message]);
                exit;
            }
        }

        return ['brand_id' => $results->getBrands()[0]->getBrandId()];
    }
    # ***DS.snippet.0.end
}
