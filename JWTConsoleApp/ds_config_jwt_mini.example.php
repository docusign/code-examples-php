<?php

$JWT_CONFIG = [
    'ds_client_id' => '{INTEGRATION_KEY_JWT}', // The app's DocuSign integration key
    'authorization_server' => 'account-d.docusign.com',
    "ds_impersonated_user_id" => '{IMPERSONATED_USER_ID}',  // the id of the user
    "private_key_file" => "private.key", // path to private key file
];

$DS_CONFIG = [
    'demo_doc_path' => '../public/demo_documents/',
    'doc_docx' => 'World_Wide_Corp_Battle_Plan_Trafalgar.docx',
    'doc_pdf' =>  'World_Wide_Corp_lorem.pdf',
    'doc_txt' =>  'Check_If_Approved.txt',
    'app_url' => 'http://localhost:8080/public', // The url of the application.
];
$GLOBALS['DS_CONFIG'] = $DS_CONFIG;
$GLOBALS['JWT_CONFIG'] = $JWT_CONFIG;
