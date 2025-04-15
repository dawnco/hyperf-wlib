<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\TcpClientPool\Imp;

use WLib\WLog;

class WrapperClient
{
    public \Swoole\Coroutine\Client $client;
    public int $useCount = 0;
    public int $lastUseTime;

    public function __construct(\Swoole\Coroutine\Client $client)
    {
        $this->client = $client;
        $this->client->set([
            "timeout" => 10,
        ]);
        $this->lastUseTime = time();
    }

    public function markUsed(): void
    {
        $this->lastUseTime = time();
        $this->useCount++;
    }

    /**
     * 客户端是否有效
     * @param int $ttl
     * @param int $maxUses
     * @return bool
     */
    public function valid(int $ttl, int $maxUses): bool
    {
        $invalid = $this->client->connected && (time() - $this->lastUseTime < $ttl) && ($this->useCount < $maxUses);

        if (!$invalid) {
            WLog::info(sprintf("连接失效重连 TCP connected %s lastUseTime %s  < c:%s? ,useCount %s < c:%s ?",
                $this->client->connected,
                time() - $this->lastUseTime,
                $ttl,
                $this->useCount,
                $maxUses,
            ));
        }

        return $invalid;
    }

    public function isReusable(): bool
    {
        if (!$this->client->connected) {
            return false;
        }

        if ($this->client->errCode > 0) {
            return false;
        }

        return true;
    }

}

