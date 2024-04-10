<?php
// ds_config.php
// 
// DocuSign configuration settings
$DS_CONFIG = [
    'quickstart' => '{QUICKSTART_VALUE}',
    'quickACG' => '{QUICK_ACG_VALUE}',
    'ds_client_id' => '{INTEGRATION_KEY_AUTH_CODE}',  // The app's DocuSign integration key
    'ds_client_secret' => '{SECRET_KEY}', // The app's DocuSign integration key's secret
    'signer_email' => '{SIGNER_EMAIL}',
    'signer_name' => '{SIGNER_NAME}',
    'app_url' => 'http://localhost:8080/public', // The url of the application.
    // Ie, the user enters  app_url in their browser to bring up the app's home page
    // Eg http://localhost/code-examples-php/public (no trailing slash) if the app is installed in a
    // development directory that is accessible via web server.
    // NOTE => You must add a Redirect URI of app_url/index.php?page=ds_callback to your Integration Key.
    'authorization_server' => 'https://account-d.docusign.com',
    'session_secret' => '{SESSION_SECRET}', // Secret for encrypting session cookie content
    'allow_silent_authentication' => true, // a user can be silently authenticated if they have an
    // active login session on another tab of the same browser
    'target_account_id' => false, // Set if you want a specific DocuSign AccountId, If false, the user's default account will be used.
    'demo_doc_path' => 'demo_documents',
    'doc_docx' => 'World_Wide_Corp_Battle_Plan_Trafalgar.docx',
    'offer_doc_docx' => 'Offer_Letter_Dynamic_Table.docx',
    'doc_html' => 'doc_1.html',
    'doc_pdf' =>  'World_Wide_Corp_lorem.pdf',
    'doc_txt' =>  'Check_If_Approved.txt',
    // Payment gateway information is optional
    'gateway_account_id' => '{GATEWAY_ACCOUNT_ID}',
    'gateway_name' => "stripe",
    'gateway_display_name' => "Stripe",
    'github_example_url' => 'https://github.com/docusign/code-examples-php/tree/master/src/Controllers/Examples',
    'documentation' => false,
    'minimum_buffer_min' => 3,
    // Manifest files
    "CodeExamplesManifest" => "https://raw.githubusercontent.com/docusign/code-examples-csharp/master/manifest/CodeExamplesManifest.json"
];

$JWT_CONFIG = [
    'ds_client_id' => '{INTEGRATION_KEY_JWT}', // The app's DocuSign integration key
    'authorization_server' => 'account-d.docusign.com',
    "ds_impersonated_user_id" => '{IMPERSONATED_USER_ID}',  // the id of the user
    "private_key_file" => "./private.key", // path to private key file
];

$GLOBALS['DS_CONFIG'] = $DS_CONFIG;
$GLOBALS['JWT_CONFIG'] = $JWT_CONFIG;
