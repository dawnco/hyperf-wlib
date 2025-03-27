<?php

declare(strict_types=1);

/**
 * 错误日志客户端
 * @date   2025-03-26
 */

namespace WLib\Lib\Center;

use WLib\WConfig;
use WLib\WLog;

class ErrorCenter
{

    /**
     * 发送错误需要处理的时候记录的错误日志
     * @param array $data 格式 查看 文档
     * @return void
     */
    public static function record(array $data): void
    {

        if (!isset($data['file'])) {
            // 获取堆栈信息
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            // 获取调用该函数的文件和行号
            $callerFile = $backtrace[0]['file'];
            $callerLine = $backtrace[0]['line'];
            $data['file'] = $callerFile;
            $data['line'] = $callerLine;
        }

        $ms = arr_get($data, "t") ?: intval(microtime(true) * 1000);
        $p = [
            "t" => strval($ms),
            "level" => (string)arr_get($data, "level", "error"),
            "requestId" => (string)arr_get($data, "requestId", ""),
            "date" => (string)(arr_get($data, "date") ?: date("Y-m-d", intval($ms / 1000))),
            "service" => (string)(arr_get($data, "service", "") ?: WConfig::get("app_name")),
            "env" => (string)arr_get($data, "env", WConfig::get("app_env")),
            "ip" => (string)arr_get($data, "ip", ""),
            "call" => (string)arr_get($data, "call", ""),
            "duration" => (int)arr_get($data, "duration", -1),
            "path" => (string)arr_get($data, "path", ""),
            "status" => (int)arr_get($data, "status", -1),
            "appId" => (int)arr_get($data, "appId", -1),
            "keyword1" => (string)arr_get($data, "keyword1", ""),
            "keyword2" => (string)arr_get($data, "keyword2", ""),
            "keyword3" => (string)arr_get($data, "keyword3", ""),
            "msg" => (string)arr_get($data, "msg", ""),
            "context" => (string)arr_get($data, "context", "{}"),
            "file" => (string)arr_get($data, "file", ""),
            "line" => (int)arr_get($data, "line", -1),
        ];

        self::send(json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


    protected static function send(string $data): void
    {
        $str = self::pack($data);
        if ($str) {
            Client::send($str, "error.log.stat.com:9823");
        }
    }

    protected static function pack(string $data): string
    {
        $length = strlen($data);
        $type = pack('n', 61);
        $remain = pack('C', 1);
        $size = pack('N', $length);

        if ($length > 10235) {
            WLog::error("ErrorCenter 数据过长", ["data" => $data]);
            return "";
        }

        return $type . $remain . $size . $data;
    }


}
