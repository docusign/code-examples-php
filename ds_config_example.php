<?php
// ds_config.php
// 
// DocuSign configuration settings
$DS_CONFIG = [
    'ds_client_id' => '{CLIENT_ID}', # The app's DocuSign integration key
    'ds_client_secret' => '{CLIENT_SECRET}', # The app's DocuSign integration key's secret
    'signer_email' => '{USER_EMAIL}',
    'signer_name' => '{USER_FULLNAME}',
    'app_url' => '{APP_URL}', // The url of the application.
    // Ie, the user enters  app_url in their browser to bring up the app's home page
    // Eg http://localhost/code-examples-php/public if the app is installed in a
    // development directory that is accessible via web server.
    // NOTE => You must add a Redirect URI of app_url/index.php?page=ds_callback to your Integration Key.
    'authorization_server' => 'https://account-d.docusign.com',
    'session_secret' => '{SESSION_SECRET}', // Secret for encrypting session cookie content
    'allow_silent_authentication' => true, // a user can be silently authenticated if they have an
    // active login session on another tab of the same browser
    'target_account_id' => false, // Set if you want a specific DocuSign AccountId, If false, the user's default account will be used.
    'demo_doc_path' => 'demo_documents',
    'doc_docx' => 'World_Wide_Corp_Battle_Plan_Trafalgar.docx',
    'doc_pdf' =>  'World_Wide_Corp_lorem.pdf',
    // Payment gateway information is optional
    'gateway_account_id' => '{DS_PAYMENT_GATEWAY_ID}',
    'gateway_name' => "stripe",
    'gateway_display_name' => "Stripe",
    'github_example_url' => 'https://github.com/docusign/code-examples-php/tree/master/src/Example/Controllers/Templates',
    'documentation' => false,
];

$JWT_CONFIG = [
    'ds_client_id' => '{CLIENT_ID}', # The app's DocuSign integration key
    'authorization_server' => 'account-d.docusign.com',
    "ds_impersonated_user_id" => '{USER_ID}',  # the id of the user
    "jwt_scope" => "signature impersonation",
    "private_key_file" => "../private.key", #path to file which hold private key or private key itself
    "private_key" => "-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----"
];

$GLOBALS['DS_CONFIG'] = $DS_CONFIG;
$GLOBALS['JWT_CONFIG'] = $JWT_CONFIG;