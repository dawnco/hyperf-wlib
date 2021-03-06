<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-05-30
 */

namespace WLib;

use WLib\Exception\AppException;
use Hyperf\Utils\Coroutine;

class WUtil
{

    /**
     * @return string
     */
    public static function uuid(): string
    {
        $str = md5(uniqid(time() . "-" . mt_rand(), true));
        $uuid = substr($str, 0, 8) . '-';
        $uuid .= substr($str, 8, 4) . '-';
        $uuid .= substr($str, 12, 4) . '-';
        $uuid .= substr($str, 16, 4) . '-';
        $uuid .= substr($str, 20, 12);
        return $uuid;
    }

    /**
     *  微秒
     * @return int
     */
    public static function microseconds(): int
    {
        return intval(microtime(true) * 1000 * 1000);
    }

    /**
     * 毫秒
     * @return int
     */
    public static function milliseconds(): int
    {
        return intval(microtime(true) * 1000);
    }

    /**
     * @param string $format
     * @param int    $timestamp
     * @return string
     */
    public static function date(string $format = 'Y-m-d H:i:s', int $timestamp = 0): string
    {
        return date($format, $timestamp ?: time());
    }

    /**
     * 创建携程
     * @param callable $callable
     * @return int
     */
    public static function create(callable $callable): int
    {
        $requestId = WCtx::requestId();
        return Coroutine::create(function () use ($callable, $requestId) {
            WCtx::setRequestId($requestId);
            call($callable);
        });
    }

}
