<?php

namespace app\helper;

class AesCipher
{
    const CIPHER = 'AES-256-CBC';
    const INIT_VECTOR_LENGTH = 16;

    public static function encrypt($secretKey, $plainText)
    {
        try {
            $initVector = bin2hex(openssl_random_pseudo_bytes(static::INIT_VECTOR_LENGTH / 2));
            $raw = openssl_encrypt(
                $plainText,
                static::CIPHER,
                $secretKey,
                OPENSSL_RAW_DATA,
                $initVector
            );
            $result = base64_encode($initVector . $raw);
            if ($result === false) {
                return new static($initVector, null, openssl_error_string());
            }
            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }
}