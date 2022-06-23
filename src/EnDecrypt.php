<?php

declare(strict_types=1);

/** 加解密
 * @author Dawnc
 * @date   2022-05-26
 */

namespace WLib;

class EnDecrypt
{

    private static string $method = 'AES-128-CBC';

    /**
     * 生成iv值
     * @return string iv值
     */
    public static function iv(): string
    {
        $len = openssl_cipher_iv_length(self::$method);
        return openssl_random_pseudo_bytes($len);
    }

    /**
     * 加密
     * @param string $str
     * @param string $iv
     * @param string $accessKey
     * @return string  base64后的字符串
     */
    public static function en(string $str, string $iv, string $accessKey): string
    {
        $raw = openssl_encrypt($str, self::$method, $accessKey, \OPENSSL_RAW_DATA, $iv);
        return $raw ?: '';
    }


    /**
     * 解密
     * @param string $str
     * @param string $iv
     * @param string $accessKey
     * @return string
     */
    public static function de(string $str, string $iv, string $accessKey): string
    {
        $raw = openssl_decrypt($str, self::$method, $accessKey, \OPENSSL_RAW_DATA, $iv);
        return $raw ?: '';
    }

}
