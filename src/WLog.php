<?php

declare(strict_types=1);

namespace WLib;

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
        if (WConfig::get('app_env') == 'production' && $name == 'debug') {
            return;
        }
        $message = $arguments[0] ?? '';
        self::record(WConfig::get('app_name', ''), $name, $message);
    }


    /**
     * 日志格式 [北京时间] [一级分类] [二级分类] [requestId] [time] msg
     * 日志例子 [2022-06-13 14:13:29] [info] [] [7e484c28-92a6-c679-4835-a2d3fa418334] [1655100809772] "hello"
     * @param string      $category 分类
     * @param string      $tag      标签
     * @param mixed       $message  日志内容
     * @param int         $time     毫秒
     * @param string|null $requestId
     * @return void
     */
    public static function record(
        string $category,
        string $tag,
        mixed $message,
        int $time = 0,
        null|string $requestId = null
    ): void {

        $requestId = $requestId ?: WCtx::requestId();
        $time = $time ?: WUtil::milliseconds();
        $dir = WConfig::get('log.dir') ?: "/tmp";

        try {
            $dateTime = WDate::getInstance();
            $dateTime->setTimestamp(intval($time / 1000));

            $date = $dateTime->format('Y-m-d');
            $file = sprintf("%s/%s-%s.log", $dir, $category, $date);

            $str = sprintf("[%s] [%s] [%s] [%s] [%s] %s\n",
                $dateTime->format("Y-m-d H:i:s"),
                $category,
                $tag,
                $requestId,
                $time,
                app_json_encode($message));
            file_put_contents($file, $str, FILE_APPEND);

        } catch (\Throwable $e) {
            $date = intval($time / 1000);
            $str = sprintf("[%s] [%s] [%s] [%s] [%s] %s\n",
                date("Y-m-d H:i:s", $date),
                "logException",
                "",
                $requestId,
                $time,
                app_json_encode($e->getMessage()));
            $file = sprintf("%s/%s-%s.log", $dir, $category, $date);
            file_put_contents($file, $str, FILE_APPEND);
        }
    }
}
