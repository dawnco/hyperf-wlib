<?php

declare(strict_types=1);

/**
 * https://zh.m.wikipedia.org/zh/ISO_3166-1
 * @author Dawnc
 * @date   2022-06-17
 */

namespace WLib\Constant;

use WLib\Exception\AppException;

class CountryConstant
{
    /**
     * 印尼
     */
    const ID = 1;

    /**
     * 尼日尼亚
     */
    const NG = 2;

    /**
     * 哥伦比亚
     */
    const CO = 3;

    /**
     * 转换为数字国家
     * @throws AppException
     */
    public static function toInt(string $code2): int
    {
        $code = strtoupper($code2);
        return match ($code) {
            'ID' => self::ID,
            'NG' => self::NG,
            'CO' => self::CO,
            default => throw new AppException("not defined country $code2"),
        };
    }
}
