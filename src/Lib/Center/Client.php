<?php

declare(strict_types=1);

namespace WLib\Lib\Center;

use WLib\WLog;

class Client
{
    private static array $client = [];

    /**
     * @param string $data        要发送是数据
     * @param string $hostAndPort $ 格式  127.0.0.1:8080
     * @return void
     */
    public static function send(string $data, string $hostAndPort): void
    {

        if (!isset(self::$client[$hostAndPort])) {
            self::$client[$hostAndPort] = stream_socket_client("udp://$hostAndPort", $errno, $error);
            if (!self::$client[$hostAndPort]) {
                WLog::error("Center 初始化客户端错误 $hostAndPort $error");
                return;
            }
            // https://www.php.net/manual/zh/function.stream-set-blocking.php
            stream_set_blocking(self::$client[$hostAndPort], false);
        }
        if (!self::$client[$hostAndPort]) {
            WLog::error("Center 客户端不存在 $hostAndPort");
            return;
        }
        @fwrite(self::$client[$hostAndPort], $data);
    }
}
