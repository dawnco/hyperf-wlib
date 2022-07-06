<?php

declare(strict_types=1);

/**
 * @see    https://segmentfault.com/a/1190000021244328
 * @author Dawnc
 * @date   2022-05-28
 */

namespace App\Service\Api\Core;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use WLib\Constant\CountryConstant;
use WLib\Exception\AppException;
use WLib\WDate;
use WLib\WRedis;

/**
 * 订单sn生成器
 */
class SnGenerator
{

    /**
     * @param string $country
     * @param int    $timestamp
     * @return int
     * @throws AppException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function gen(string $country, int $timestamp): int
    {
        $countryInt = CountryConstant::toInt($country);

        if ($countryInt < 1 || $countryInt > 9) {
            throw new AppException("sn country length error");
        }

        $wDate = new WDate($country);
        $ymd = $wDate->setTimestamp($timestamp)->format('Ymd');
        $dateStr = $wDate->setTimestamp($timestamp)->format('Y,n,j');
        $todayBegin = $wDate->setTimestamp($timestamp)->dayBegin()->getTimestamp();

        list($y, $m, $d) = array_map('intval', explode(',', $dateStr));
        $snQuarter = (int)floor(($timestamp - $todayBegin) / 60 / 15);

        $snQuarter = str_pad((string)$snQuarter, 2, "0", STR_PAD_LEFT);
        $key = sprintf("sys:generator:sn:%s:%s", $ymd, $snQuarter);
        $incr = self::incr($key);

        if ($incr > 9999) {
            throw new AppException("sn sequence is big than 9999");
        }

        $incr = str_pad($incr, 4, "0", STR_PAD_LEFT);

        $snYear = ($y - 2022) % 10;
        $snMonth = $m - 1;
        $snMonth = ($snMonth <= 9) ? $snMonth : 9;
        $snDay = $d;
        if ($m == 10) {
            --$snDay;
        } else {
            if ($m == 11) {
                $snDay += 30;
            } else {
                if ($m == 12) {
                    $snDay += 60;
                }
            }
        }
        $snDay = str_pad(strval($snDay), 2, "0", STR_PAD_LEFT);

        $str = sprintf('%s%s%s%s%s%s', $countryInt, $snYear, $snMonth, $snDay, $snQuarter, $incr);

        return intval($str);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function incr(string $key): string
    {

        $redis = WRedis::connection();

        $script = <<<EOT
            -- local key = 'inc-id:' .. KEYS[1]
            local key = ARGV[1]
            local incr = redis.call('incr', key)
            redis.call('expire', key, 1800)
            return incr
EOT;

        $sha = $redis->script('load', $script);
        //echo $redis->getLastError();
        $ret = $redis->evalSha($sha, [
            $key
        ]);
        return (string)$ret;
    }

    public static function meta(int $sn): array
    {
        $str = strval($sn);
        $country = substr($str, 0, 1);
        $year = substr($str, 1, 1);
        $month = substr($str, 2, 1);
        $day = substr($str, 3, 2);
        $quarter = substr($str, 5, 2);
        $incr = substr($str, 7);

        $rMonth = $month + 1;
        $rDay = $day;
        if ($month == 9) {
            if ($day >= 0 && $day <= 30) {
                $rDay = $day + 1;
            } elseif ($day >= 31 && $day <= 60) {
                $rMonth = $month + 2;
                $rDay = $day - 30;
            } elseif ($day >= 61 && $day <= 92) {
                $rMonth = $month + 3;
                $rDay = $day - 60;
            }
        }

        // 10月  $day 在 [0, 30],  11月 $day在 [31]

        $year = (int)$year + 2022;
        $month = (int)$rMonth;
        return [
            "country" => (int)$country,
            "year" => $year,
            "month" => $month,
            "day" => (int)$rDay,
            "quarter" => (int)ltrim($quarter, "0"),
            "ym" => str_pad(strval($year - 2000), 2, "0", STR_PAD_LEFT) . str_pad(strval($month), 2, "0", STR_PAD_LEFT),
            "incr" => (int)$incr,
        ];

    }
}
