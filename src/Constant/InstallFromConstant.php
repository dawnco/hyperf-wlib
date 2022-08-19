<?php

declare(strict_types=1);

/**
 * 安装来源
 * @author Dawnc
 * @date   2022-06-17
 */

namespace WLib\Constant;

class InstallFromConstant
{
    /**
     * google 广告
     */
    const GG = 1;

    /**
     * FB 广告
     */
    const FB = 2;

    /**
     * Tiktok 广告
     */
    const TT = 3;

    /**
     * 自然
     */
    const NA = 4;

    /**
     * 贷超
     */
    const MA = 5;


    /**
     * 获取渠道字符串
     * @param int $number
     * @return string
     */
    public static function intToName(int $number): string
    {
        return match ($number) {
            self::GG => "GG",
            self::FB => "FB",
            self::TT => "TT",
            self::NA => "NA",
            self::MA => "MA",
            default => '',
        };
    }
}
