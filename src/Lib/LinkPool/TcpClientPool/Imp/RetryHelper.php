<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\TcpClientPool\Imp;


use WLib\WLog;

class RetryHelper
{
    public static function withRetry(callable $fn, int $maxRetries = 2): int
    {
        $attempts = 0;
        while ($attempts <= $maxRetries) {

            $sendBytes = $fn();

            if ($sendBytes) {
                return $sendBytes;
            }

            $attempts++;
            if ($attempts >= $maxRetries) {
                return 0;
            }
            \Swoole\Coroutine::sleep(0.05);
            WLog::error(sprintf("重试 发送TCP数据 次数:%s", $attempts));
        }
        return 0;
    }
}
