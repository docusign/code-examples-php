<?php

namespace Example\Services;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Configuration;



class utils

{
/**
     * Checks an account to determine if it is cfr pt 11 enabled
     */
    public function isCFR($access_token, $account_id, $base_path)
    {
        try {
            $config = new Configuration();
            $config->setHost($base_path);
            $config->addDefaultHeader('Authorization', 'Bearer ' . $access_token);
            $this->api_client = new ApiClient($config);

            $accountsApi = new AccountsApi($this->api_client);
            $accountDetails = $accountsApi->getAccountInformation($account_id);
            var_dump($accountDetails["status21_cfr_part11"]);
            return $accountDetails["status21_cfr_part11"];
        } catch(ApiException $ex) {
            return null;
        }
    }



}
    ?>