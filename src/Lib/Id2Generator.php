<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2023-10-25
 */

namespace WLib\Lib;

use WLib\Exception\AppException;
use WLib\WRedis;

class Id2Generator
{

    /**
     * @var int 系统编码 1-9
     */
    protected static int $systemCode = 1;

    /**
     * 初始年份
     * @var int
     */
    protected static int $initialYear = 2000;

    /**
     * 生成订单号  时区要指定成当地时区
     * @param $timestamp  int  秒时间戳  未指定用 系统的
     * @param $systemCode int  系统编码 1-9
     * @return int
     */
    public static function gen(int $timestamp = 0, int $systemCode = 1): int
    {
        if (!$timestamp) {
            $timestamp = time();
        }
        [$year, $month, $day] = explode('-', date('Y-n-d', $timestamp));
        $yearCode = $year - self::$initialYear;
        $diffMonth = (string)(12 - $month);
        $monthCode = str_pad($diffMonth, 2, '0', STR_PAD_LEFT);
        $beginSecond = strtotime(date('Y-m-d', $timestamp));
        $diffSecond = $timestamp - $beginSecond;
        $diffSecondCode = str_pad("{$diffSecond}", 5, '0', STR_PAD_LEFT);
        $key = "ID2GEN:{$systemCode}:{$timestamp}";
        $incr = self::incr($key);
        $incr = str_pad("{$incr}", 4, '0', STR_PAD_LEFT);
        return (int)($systemCode . "{$yearCode}{$day}{$incr}{$monthCode}{$diffSecondCode}");
    }

    /**
     * 解析
     * @param int|string $id
     * @return array ["ym" => "2304", ""]
     */
    public static function parse(int|string $id): array
    {
        $string = (string)$id;
        $systemCode = (int)substr($string, 0, 1); // 系统编码
        $year = (int)substr($string, 1, 2) + self::$initialYear; // 年份编码
        $day = (int)substr($string, 3, 2); // 日期编码
        $incr = (int)substr($string, 5, 4); // 自增编码
        $month = 12 - (int)substr($string, 9, 2); // 月份编码
        $diffSecond = (int)substr($string, 11, 5); // 当日秒编码
        $timestamp = strtotime("$year-$month-$day") + $diffSecond;
        $ym = sprintf("%s%s", str_pad(strval($year - 2000), 2, "0", STR_PAD_LEFT),
            str_pad(strval($month), 2, "0", STR_PAD_LEFT));

        return compact('systemCode', 'timestamp', 'ym', 'year', 'month', 'day', 'incr', 'diffSecond');
    }

    /**
     * 自增
     * @param string $key
     * @return int
     */
    protected static function incr(string $key): int
    {
        try {
            $redis = WRedis::connection('idGenerator');
            $script = <<<EOT
            -- local key = {$key} .. KEYS[1]
            local key = ARGV[1]
            local incr = redis.call('incr', key)
            redis.call('expire', key, 60)
            return incr
EOT;
            $sha = $redis->script('load', $script);
            return $redis->evalSha($sha, [
                $key
            ]);
        } catch (\Throwable $throwable) {
            throw new AppException("ID2生成自增异常");
        }

    }
}
