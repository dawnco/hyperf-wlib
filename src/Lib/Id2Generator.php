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
        $key = "ID2GEN:{$systemCode}:{$timestamp}";
        $incr = self::incr($key);
        $str = sprintf("%s%s%s", $systemCode, strrev((string)$timestamp), $incr);
        return intval($str);

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
        $timestamp = (int)strrev(substr($string, 1, 10)); // 年份编码
        $incr = (int)substr($string, 11); // 自增
        $year = (int)date("Y", $timestamp);
        $month = (int)date("n", $timestamp);
        $day = (int)date("j", $timestamp);
        $ym = date("ym", $timestamp);

        return compact('systemCode', 'timestamp', 'ym', 'year', 'month', 'day', 'incr');
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
