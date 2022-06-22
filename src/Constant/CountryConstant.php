<?php

declare(strict_types=1);

/**
 * https://zh.m.wikipedia.org/zh/ISO_3166-1
 * @author Dawnc
 * @date   2022-06-17
 */

namespace WLib\Constant;

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

    public static function toId(string $code2): string
    {
        $code = strtoupper($code2);
        switch ($code) {
            case 'id':
                return self::ID;
            case 'ng':
                return self::NG;
            case 'co':
                return self::CO;
        }
    }
}
