<?php

namespace DocuSign\Services\Examples\Connect;

class ValidateUsingHmacService
{
    public static function computeHash($secret, $payload)
    {
        $hexHash = hash_hmac(
            'sha256',
            $payload,
            utf8_encode($secret)
        );
        $base64Hash = base64_encode(hex2bin($hexHash));

        return $base64Hash;
    }

    public static function isValid($secret, $payload, $verify)
    {
        return hash_equals($verify, self::ComputeHash($secret, $payload));
    }
}
