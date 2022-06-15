<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-09
 */

namespace WLib;

class WConfig
{
    public static function get($key, $default = null)
    {
        return config($key, $default);
    }
}
