<?php

use DocuSign\eSign\Configuration;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;



require "vendor/autoload.php";
require "ds_config.php";


$rsaPrivateKey = file_get_contents($GLOBALS['JWT_CONFIG']['private_key_file']);
$integration_key = $GLOBALS['JWT_CONFIG']['ds_client_id'];
$impersonatedUserId = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'];
$scopes = "signature impersonation";


$config = new Configuration();
$apiClient = new ApiClient($config);

// Collect user information through prompts
echo "Welcome to the JWT Code example!\n";
echo "Enter the signer's email address: \n";
$signer_email = trim(fgets(STDIN));

echo "Enter the signer's name: \n";
$signer_name = trim(fgets(STDIN));

echo "Enter the carbon copy's email address: \n";
$cc_email = trim(fgets(STDIN));

echo "Enter the carbon copy's name: \n";
$cc_name = trim(fgets(STDIN)); 


// get the information from the app.config file




try {
    $apiClient->getOAuth()->setOAuthBasePath("account-d.docusign.com");
    $response = $apiClient->requestJWTUserToken($integration_key, $impersonatedUserId, $rsaPrivateKey, $scopes, 60);



} catch (\Throwable $th) {
    var_dump($th);
    // we found consent_required in the response body meaning first time consent is needed
    if (strpos($th->getMessage(), "consent_required") !== false) {
        $authorizationURL = 'https://account-d.docusign.com/oauth/auth?' . http_build_query([
            'scope'         => $scopes,
            'redirect_uri'  => 'https://httpbin.org/get',
            'client_id'     => $integration_key,
            'response_type' => 'code'
        ]);

        echo "It appears that you are using this integration key for the first time.  Please visit the following link to grant consent authorization.\n\n";
        echo $authorizationURL;


        exit();
    }
}

// We've gotten a JWT token, now we can use it to make API calls
if (isset($response)) {
    $access_token = $response[0]['access_token'];
    // retrieve our API account Id
    
    $info = $apiClient->getUserInfo($access_token);
    $account_id = $info[0]["accounts"][0]["account_id"];

    // Instantiate the API client again with the default header set to the access token
    $config->setHost("https://demo.docusign.net/restapi");
    $config->addDefaultHeader('Authorization', 'Bearer ' . $access_token);
    $apiClient = new ApiClient($config);

    try {
        // Create an envelope definition object
        $envelope = new \DocuSign\eSign\Model\EnvelopeDefinition();
        $envelope->setEmailSubject("Please sign this document set");
        $envelope->setStatus("sent");

        // Crete a tab object
        $signHere = new \DocuSign\eSign\Model\SignHere();
        $signHere->setDocumentId("1");
        $signHere->setPageNumber("1");
        $signHere->setXPosition("191");
        $signHere->setYPosition("148");

        $tabs = new \DocuSign\eSign\Model\Tabs();
        $tabs->setSignHereTabs(array($signHere));

        // Set recipients
        $signer = new \DocuSign\eSign\Model\Signer();
        $signer->setEmail($signer_email);
        $signer->setName($signer_name);
        $signer->setRecipientId("1");
        $signer->setTabs($tabs);

        $cc = new \DocuSign\eSign\Model\CarbonCopy();
        $cc->setEmail($cc_email);
        $cc->setName($cc_name);
        $cc->setRecipientId("2");

        $recipients = new \DocuSign\eSign\Model\Recipients();
        $recipients->setSigners(array($signer));
        $recipients->setCarbonCopies(array($cc));
        
        $envelope->setRecipients($recipients);

        // Add document
        $document = new \DocuSign\eSign\Model\Document();
        $document->setDocumentBase64("VGhhbmtzIGZvciByZXZpZXdpbmcgdGhpcyEKCldlJ2xsIG1vdmUgZm9yd2FyZCBhcyBzb29uIGFzIHdlIGhlYXIgYmFjay4=");
        $document->setName("doc1.txt");
        $document->setFileExtension("txt"); 
        $document->setDocumentId("1");
        $envelope->setDocuments(array($document));  

        // Send envelope
        $envelopesAPI = new \DocuSign\eSign\Api\EnvelopesApi($apiClient);
        $envelope = $envelopesAPI->createEnvelope($account_id, $envelope);
        echo "Successfully sent envelope with envelopeId: " . $envelope->getEnvelopeId() . "\n";


    } catch (ApiException $e) {
        var_dump($e);
        exit;
    }
}
