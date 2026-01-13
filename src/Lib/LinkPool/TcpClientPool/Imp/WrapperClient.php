<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\TcpClientPool\Imp;

use Swoole\Coroutine;
use Swoole\Coroutine\Client;
use WLib\WLog;

class WrapperClient
{
    public \Swoole\Coroutine\Client $client;
    public int $useCount = 0;
    public int $lastUseTime;

    public function __construct(protected string $host, protected int $port)
    {
        $client = new Client(SWOOLE_SOCK_TCP);
        $ok = $client->connect($this->host, $this->port, 30);
        if (!$ok) {
            //连接失败重试一次
            $client->close();
            Coroutine::sleep(0.05);
            WLog::error(sprintf("尝试重新连接 %s:%s", $this->host, $this->port));
            $client->connect($this->host, $this->port, 30);
        }

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
            WLog::debug(sprintf("连接失效重连 TCP %s:%s connected %s lastUseTime %s ago < c:%s? ,useCount %s < c:%s ?",
                $this->host,
                $this->port,
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

