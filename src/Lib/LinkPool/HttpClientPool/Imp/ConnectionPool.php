<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool\Imp;

use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Client;
use WLib\WLog;

class ConnectionPool
{
    private Channel $channel;
    private int $ttl;
    private bool $ssl;
    private int $maxUses;

    private string $host;
    private int $port;


    public function __construct(
        string $host,
        int $port,
        bool $ssl,
        int $size,
        int $ttl,
        int $maxUses
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->ttl = $ttl;
        $this->ssl = $ssl;
        $this->maxUses = $maxUses;
        $this->channel = new Channel($size);
    }

    public function createClient(): WrapperClient
    {
        return new WrapperClient(new Client($this->host, $this->port, $this->ssl));
    }

    public function get(): WrapperClient
    {
        if ($this->channel->isEmpty()) {
            return $this->createClient();
        } else {
            /**
             * @var $wrapper WrapperClient
             */
            $wrapper = $this->channel->pop();
            if (!$wrapper->valid($this->ttl, $this->maxUses)) {
                $wrapper->client->close();
                return $this->createClient();
            }
            $wrapper->markUsed();
            return $wrapper;
        }
    }

    public function put(WrapperClient $wrapper): void
    {
        if (!$wrapper->isReusable()) {
            WLog::error(sprintf("连接不可复用了 %s:%s", $this->host, $this->port));
            $wrapper->client->close();
            return;
        }

        if ($this->channel->isFull()) {
            WLog::error(sprintf("连接已经满了 %s:%s", $this->host, $this->port));
            $wrapper->client->close();
            return;
        }

        $this->channel->push($wrapper);

    }
}
