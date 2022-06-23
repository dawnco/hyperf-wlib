<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-20
 */

namespace WLib;

use App\Exception\AppException;
use WLib\Constant\CountryConstant;

class WTable
{
    /**
     * 日期名称
     * @param int $timestamp
     * @return string
     * @throws AppException
     */
    public static function name(int $timestamp): string
    {
        $country = WConfig::get('app_country');
        if (!$country) {
            throw new AppException("app_country config not set");
        }
        return WDate::getInstance(CountryConstant::toId($country))->setTimestamp($timestamp)->format("ym");
    }
}
