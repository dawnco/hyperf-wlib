<?php

declare(strict_types=1);

/**
 * 日志客户端
 * @date   2025-03-26
 */

namespace WLib\Lib\Center;

use WLib\WLog;

class LogCenter
{

    /**
     * 记录到sls
     * @param string $store     那个store
     * @param array  $data      key和value的类型必须是字符串 格式 ["key"=>"value", "name"=>"jard", "age"=>"25"]
     * @param int    $timestamp 秒时间戳 默认当前秒
     * @return void
     */
    public static function record(
        string $store,
        array $data,
        int $timestamp = 0
    ): void {
        self::log($store, $data, "log.stat.com:8844", $timestamp);
    }

    public static function GlobalRecord(
        string $store,
        array $data,
        int $timestamp = 0
    ): void {
        self::log($store, $data, "global.log.stat.com:8844", $timestamp);
    }

    /**
     * 记录到sls 可以指定 host
     * @param string $store     那个store
     * @param array  $data      key和value的类型必须是字符串 格式 ["key"=>"value", "name"=>"jard", "age"=>"25"]
     * @param string $host      格式  127.0.0.1:8080
     * @param int    $timestamp 秒时间戳 默认当前秒
     * @return void
     */
    public static function log(
        string $store,
        array $data,
        string $host,
        int $timestamp = 0,
    ): void {

        $kv = [];
        foreach ($data as $k => $v) {
            $kv[] = [
                "key" => $k,
                "val" => $v,
            ];
        }
        self::send(json_encode([
            "t" => $timestamp ?: time(), // 时间戳 秒
            "store" => $store, // 那个store
            "kv" => $kv
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $host);
    }

    protected static function send(string $data, string $host): void
    {

        $str = self::pack($data);
        if ($str) {
            Client::send($str, $host);
        }

    }

    protected static function pack(string $data): string
    {
        $length = strlen($data);
        $type = pack('n', 60);
        $remain = pack('C', 1);
        $size = pack('N', $length);

        if ($length > 10235) {
            WLog::error("LogCenter 数据过长", ["data" => $data]);
            return "";
        }

        return $type . $remain . $size . $data;
    }


}
