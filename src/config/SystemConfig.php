<?php

namespace Config;

class SystemConfig
{
    /**
     * cipher method
     *
     * @var string
     */
    private static string $ciphering = "AES-128-CTR";

    /**
     * Vector for encryption
     *
     * @var string
     */
    private static string $encryption_iv = '1234567891011121';

    /**
     * Encode the app's name and return the string obtained
     *
     * @param string $appName
     * @return string
     */
    public static function encodeAppName(string $appName): string
    {
        $options = 0;
        return openssl_encrypt($appName, self::$ciphering,
            ENCRYPTION_KEY, $options, self::$encryption_iv);
    }

    /**
     * Decode the encryption string and return the string obtained
     *
     * @param string $encryption
     * @return string
     */
    public static function decodeAppName(string $encryption): string
    {
        $options = 0;
        return openssl_decrypt ($encryption, self::$ciphering,
            ENCRYPTION_KEY, $options, self::$encryption_iv);
    }
}