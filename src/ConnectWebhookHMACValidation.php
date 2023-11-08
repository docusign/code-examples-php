<?php

namespace DocuSign;

abstract class ConnectWebhookHMACValidation
{
    /*
     * Useful reference: https://www.php.net/manual/en/function.hash-hmac.php
     * NOTE: Currently DocuSign only supports SHA256.
     */
    private static function computeHash($secret, $payload)
    {
        $hexHash = hash_hmac('sha256', $payload, utf8_encode($secret));
        $base64Hash = base64_encode(hex2bin($hexHash));
        return $base64Hash;
    }
    public static function hashIsValid($secret, $payload, $verify)
    {
        return hash_equals($verify, self::computeHash($secret, $payload));
    }
}
