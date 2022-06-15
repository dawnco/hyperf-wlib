<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-05-29
 */

namespace App\Lib;

use App\Exception\AppException;
use App\Helper\Redis;

/**
 */
class IdGenerator
{

    private const START_TIMESTAMP = 1577808000000; // 2020-01-01 毫秒

    /**
     * @param $dataCenterId 数据中心ID 范围 0 -31
     * @param $workerId     服务器ID 范围 0 -31
     * @return int
     */
    public static function id($dataCenterId = 1, $workerId = 1): int
    {
        $interval = intval(microtime(true) * 1000) - self::START_TIMESTAMP;  // 42位
        $dataCenterId = 1; // 数据中心或者国家 最大值 31
        $workerId = 1; // 服务器id 最大值  31
        //$sequence = rand(0, 4095);   // 顺序ID  最大值 4095
        $sequence = self::incr($interval);
        return ($interval << 22) | ($dataCenterId << 17) | ($workerId << 12) | $sequence;
    }

    private static function incr(int $time): int
    {
        $redis = Redis::connection();
        $key = 'IdGenerator:incr:' . $time;
        $inc = $redis->incr($key);
        if ($inc > 4095) {
            throw new AppException("IdGenerator incr is big then 4095");
        }
        $redis->expire($key, 60);
        return $inc;
    }

    public static function meta($id): array
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