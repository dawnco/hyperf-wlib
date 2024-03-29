<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-05-29
 */

namespace WLib\Lib;

use WLib\Exception\AppException;
use WLib\WRedis;


/**
 * @deprecated  使用 Id2Generator
 */
class IdGenerator
{

    private const START_TIMESTAMP = 1577808000000; // 2020-01-01 毫秒

    /**
     * @param $dataCenterId int  数据中心ID 范围 0 -31  对应的国家int  使用 CountryConstant::toInt 得到
     * @param $workerId     int    服务器ID 范围 0 -31
     * @return int
     * @throws AppException
     */
    public static function id(int $dataCenterId = 1, int $workerId = 1): int
    {
        $interval = intval(microtime(true) * 1000) - self::START_TIMESTAMP;  // 42位
        $dataCenterId = 1; // 数据中心或者国家 最大值 31
        $workerId = 1; // 服务器id 最大值  31
        //$sequence = rand(0, 4095);   // 顺序ID  最大值 4095
        $sequence = self::incr($dataCenterId, $workerId, $interval);
        return ($interval << 22) | ($dataCenterId << 17) | ($workerId << 12) | $sequence;
    }

    /**
     * @throws AppException
     */
    private static function incr(int $dataCenterId, int $workerId, int $time): int
    {
        $redis = WRedis::connection("idGenerator");
        $key = sprintf('sys:generator:id:%s:%s:%s', $dataCenterId, $workerId, $time);
        $inc = $redis->incr($key);
        $redis->expire($key, 60);
        if ($inc > 4095) {
            throw new AppException("IdGenerator incr is big then 4095");
        }
        return $inc;
    }

    public static function meta(int $id): array
    {
        $bin = base_convert((string)$id, 10, 2);
        $bin = str_pad($bin, 64, '0', STR_PAD_LEFT);

        $interval = substr($bin, 0, 42);
        $dataCenterId = substr($bin, 42, 5);
        $workerId = substr($bin, 47, 5);
        $sequence = substr($bin, 52);

        return [
            'interval' => bindec($interval) + self::START_TIMESTAMP,
            'dataCenterId' => bindec($dataCenterId),
            'workerId' => bindec($workerId),
            'sequence' => bindec($sequence),
        ];
    }
}
