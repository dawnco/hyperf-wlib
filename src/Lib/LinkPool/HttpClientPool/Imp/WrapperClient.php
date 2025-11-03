<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool\Imp;

use Swoole\Coroutine\Http\Client;
use WLib\WLog;

class WrapperClient
{
    public \Swoole\Coroutine\Http\Client $client;
    public int $useCount = 0;
    public int $lastUseTime;

    public function __construct(protected string $host, protected int $port, protected bool $ssl)
    {
        $this->client = new Client($this->host, $this->port, $this->ssl);
        $this->client->set([
            "timeout" => PoolCnf::$httpTimeout,
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
            WLog::info(sprintf("连接失效重连 HTTP %s:%s connected %s lastUseTime %s ago  < c:%s? ,useCount %s < c:%s ?",
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

        if ($this->client->errCode > 0 || $this->client->statusCode <= 0) {
            return false;
        }

        if (isset($this->client->headers['connection'])
            && strtolower($this->client->headers['connection']) === 'close') {
            return false;
        }

        return true;
    }

}

