<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-20
 */

namespace WLib;

class WTable
{
    /**
     * 日期名称
     * @param int $timestamp
     * @return string
     */
    public static function name(int $timestamp): string
    {
        return WDate::getInstance('cn')->setTimestamp($timestamp)->format("ym");
    }
}
