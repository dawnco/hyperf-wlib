<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-09
 */

namespace WLib;

class WStr
{
    public static function base64urlEncode($str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public static function base64urlDecode($str): string
    {
        return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
    }
}
