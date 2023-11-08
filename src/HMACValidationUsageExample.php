<?php

namespace DocuSign;

$hmac = new ConnectWebhookHMACValidation();

if ($hmac::HashIsValid("{DocuSign HMAC private key}", file_get_contents("payload.txt"), "{JSON response Signature}")) {
    echo "Signature matches the HMAC key provided";
} else {
    echo "Signature does not match the HMAC key provided";
}
