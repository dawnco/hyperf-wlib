<?php

declare(strict_types=1);

/**
 * EC客户端
 * @date   2025-03-26
 */

namespace WLib\Lib\Center;

use WLib\WConfig;
use WLib\WLog;

class EventCenter
{

    /**
     * 发送事件数据
     * @param string $name      事件名称
     * @param array  $params    key的类型必须是字符串, value 类型任意 格式 ["key1"=>1, "key2"=>"value2"]
     * @param string $requestId
     * @param int    $timestamp 毫秒时间戳 默认 当前毫秒
     * @param string $topic     指定写入那个kafka的
     * @param string $from      来自那个服务 默认取配置的  app_name
     * @return void
     */
    public static function app(
        string $name,
        array $params = [],
        string $requestId = "",
        int $timestamp = 0,
        string $topic = "",
        string $from = ""
    ): void {
        self::send(json_encode([
            "name" => $name,
            "_topic_" => $topic,
            "requestId" => $requestId,
            "from" => strval($from ?: WConfig::get("app_name")),
            "country" => strval(WConfig::get("app_country") ?: ""),
            "timestamp" => $timestamp ?: intval(microtime(true) * 1000),
            "params" => $params ?: null
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected static function send(string $data): void
    {
        $str = self::pack($data);
        if ($str) {
            Client::send($str, "center.stat.com:9820");
        }
    }

    protected static function pack(string $data): string
    {
        $length = strlen($data);
        $type = pack('n', 55);
        $remain = pack('C', 1);
        $size = pack('n', $length);

        if ($length > 65500) {
            WLog::error("EventCenter 数据过长", ["data" => $data]);
            return "";
        }

        return $type . $remain . $size . $data;
    }


}
