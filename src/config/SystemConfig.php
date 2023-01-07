<?php

namespace Config;

use App\Services\AuthService;
use Exception;

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
        return openssl_decrypt($encryption, self::$ciphering,
            ENCRYPTION_KEY, $options, self::$encryption_iv);
    }

    /**
     * Converts a base64 string to image file and saves it into the specified route
     *
     * @param string $base64String It must be in the format "data:image/png;base64,<<imageString>>"
     * @return string
     * @throws Exception
     */
    public static function saveBase64Image(string $base64String): string
    {
        /*+---------------------------------+
        * | Separates the baseString into   |
        * | data[0] = data:image/ext;base64 |
        * | data[1] = image string          |
        +-----------------------------------+*/
        $data = explode(',', $base64String);

        $data[0] = explode(';', $data[0])[0];
        $ext = str_replace('data:image/', ".", $data[0]);

        $filename = md5(time() . uniqid()) . $ext;

        $decoded = base64_decode($data[1]);

        $route = $_ENV['API_FILES_ROOT'] . AuthService::getAppName() . '/images/users';

        $fullRoute = '../../..' . $route . '/' . $filename;
        $fullPath = $_SERVER['HTTP_HOST'] . $route . '/' . $filename;

        $status = file_put_contents($fullRoute, $decoded);

        if (!$status)
            throw new Exception('Error saving image');

        return $fullPath;
    }

    /**
     * Remove a file in the path defined into the filename parameter
     *
     * @param string $filename
     * @return void
     */
    public static function deleteFile(string $filename): void
    {
        $filename = str_replace($_SERVER['HTTP_HOST'], "", $filename);
        $filename = "../../.." . $filename;
        unlink($filename);
    }
}