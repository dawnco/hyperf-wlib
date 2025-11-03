<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\TcpClientPool;

use WLib\Lib\LinkPool\TcpClientPool\Imp\PoolManager;
use WLib\Lib\LinkPool\TcpClientPool\Imp\RetryHelper;
use WLib\WLog;

class TcpClientPool
{

    /**
     * @param string $host
     * @param int    $port
     * @param string $data
     * @return int  发送了多少字节
     */
    public static function send(string $host, int $port, string $data, array $options = []): int
    {
        $sendLen = RetryHelper::withRetry(function () use ($host, $port, $data, $options) {
            $pool = PoolManager::getPool($host, $port, $options);
            $wrapper = $pool->get();
            $sendLen = $wrapper->client->send($data, 30);
            if ($sendLen && $sendLen == strlen($data)) {
                // 发送成功
                $pool->put($wrapper);
                return $sendLen;
            } else {
                $wrapper->client->close();
                return 0;
            }
        }, $options['maxRetries'] ?? 1);

        if (!$sendLen || $sendLen == strlen($data)) {
            WLog::error(sprintf("发送 TCP 到 %s:%s 失败", $host, $port));
        }

        return $sendLen;

    }
}
