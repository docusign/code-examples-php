<?php

use Example\Services\SignatureClientService;
use Example\Services\Examples\eSignature\SigningViaEmailService;

use DocuSign\eSign\Client\ApiClient;

require "vendor/autoload.php";
require "ds_config_jwt_mini.php";


$integration_key = $GLOBALS['JWT_CONFIG']['ds_client_id'];
$impersonatedUserId = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'];
$scopes = "signature impersonation";

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

// exits out of the application if our private key file is not found
try {
    $rsaPrivateKey = file_get_contents($GLOBALS['JWT_CONFIG']['private_key_file']);
}
catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

restore_error_handler();


$args = [];


try {
    // Collect user information through prompts
    echo "Welcome to the JWT Code example!\n\n";
    echo "Enter the signer's email address: ";
    $args['envelope_args']['signer_email'] = trim(fgets(STDIN));

    echo "Enter the signer's name: ";
    $args['envelope_args']['signer_name'] = trim(fgets(STDIN));

    echo "Enter the carbon copy's email address: ";
    $args['envelope_args']['cc_email'] = trim(fgets(STDIN));

    echo "Enter the carbon copy's name: ";
    $args['envelope_args']['cc_name'] = trim(fgets(STDIN));

    $args['envelope_args']['status'] = "sent";

    // these are extra arguments used for the html document in SigningViaEmailService
    $args['envelope_args']['item'] = "wafer biscuit";
    $args['envelope_args']['quantity'] = "60";
    $apiClient = new ApiClient();
    $apiClient->getOAuth()->setOAuthBasePath("account-d.docusign.com");
    $response = $apiClient->requestJWTUserToken($integration_key, $impersonatedUserId, $rsaPrivateKey, $scopes, 60);
} catch (\Throwable $th) {
    var_dump($th);
    // we found consent_required in the response body meaning first time consent is needed
    if (strpos($th->getMessage(), "consent_required") !== false) {
        $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' . http_build_query([
            'scope'         => $scopes,
            'redirect_uri'  => $GLOBALS['DS_CONFIG']['app_url'] . '/index.php?page=ds_callback',
            'client_id'     => $integration_key,
            'response_type' => 'code'
        ]);

        echo "It appears that you are using this integration key for the first time.  Please visit the following link to grant consent authorization.\n\n";
        echo $authorizationURL;
        exit;
    }
}

// We've gotten a JWT token, now we can use it to make API calls
if (isset($response)) {
    $access_token = $response[0]['access_token'];
    // retrieve our API account Id

    $info = $apiClient->getUserInfo($access_token);
    $account_id = $info[0]["accounts"][0]["account_id"];
    $args['base_path'] = "https://demo.docusign.net/restapi";
    $args['account_id'] = $account_id;
    $args['ds_access_token'] = $access_token;




    $clientService = new SignatureClientService($args);
    $demoDocsPath =  $GLOBALS['DS_CONFIG']['demo_doc_path'];

    try {
        $callAPI = new SigningViaEmailService();
        $result = $callAPI->signingViaEmail($args, $clientService, $demoDocsPath);

        echo "Successfully sent envelope with envelope ID: " . $result['envelope_id'] . "\n";
    } catch (\Throwable $th) {
        var_dump($th);
        exit;
    }
}
