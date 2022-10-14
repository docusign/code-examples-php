<?php

class DocuSign_HMAC
{
    /*
     * Useful reference: https://www.php.net/manual/en/function.hash-hmac.php
     * NOTE: Currently DocuSign only supports SHA256.
     */
    private static function ComputeHash($secret,$payload)
    {
        $hexHash = hash_hmac('sha256',$payload,utf8_encode($secret));
        $base64Hash = base64_encode(hex2bin($hexHash));
        return $base64Hash;
    }
    public static function HashIsValid($secret,$payload,$verify)
    {
        return hash_equals($verify, self::ComputeHash($secret,$payload));
    }
}


$hmac = new DocuSign_HMAC();

if ($hmac::HashIsValid("{DocuSign HMAC private key}",file_get_contents("payload.txt"), "{JSON response Signature}")){
    echo "Signature matches the HMAC key provided";
}

else
{

    echo "Signature does not match the HMAC key provided";
}
