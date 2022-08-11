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


    public static function intToName(int $number)
    {
        switch ($number) {
            case self::GG:
                return "GG";
            case self::FB:
                return "FB";

        }
    }
}
