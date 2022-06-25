<?php

declare(strict_types=1);

namespace WLib;

use WLib\Log\StdoutLogger;

/**
 * @method static debug(string $msg)
 * @method static error(string $msg)
 * @method static info(string $msg)
 * Class WLog
 * @package WLib
 */
class WLog
{

    public static function __callStatic($name, $arguments)
    {
        $message = $arguments[0] ?? '';
        StdoutLogger::get()->$name($message);
    }

    /**
     * 日志格式 [北京时间] [服务] [一级分类] [二级分类] [requestId] [time] msg
     * 日志例子 [2022-06-13 14:13:29] [service] [info] [] [7e484c28-92a6-c679-4835-a2d3fa418334] [1655100809772] "hello"
     * @param string      $category 分类
     * @param mixed       $message  日志内容
     * @param string      $tag      标签
     * @param int         $time     毫秒
     * @param string|null $requestId
     * @return void
     */
    public static function record(
        string $category,
        mixed $message,
        string $tag = '',
        int $time = 0,
        null|string $requestId = null
    ): void {

        $context = [
            'category' => $category,
            'tag' => $tag,
            'requestId' => $requestId ?: WCtx::requestId(),
            'time' => $time ?: WUtil::milliseconds(),
            'WLOG' => true,
        ];

        StdoutLogger::get()->info(app_json_encode($message), $context);

    }
}
