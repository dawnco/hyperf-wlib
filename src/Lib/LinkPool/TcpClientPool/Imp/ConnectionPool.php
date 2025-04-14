<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\TcpClientPool\Imp;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Client;
use WLib\WLog;

class ConnectionPool
{
    private Channel $channel;
    private int $ttl;
    private int $maxUses;

    private string $host;
    private int $port;


    public function __construct(
        string $host,
        int $port,
        int $size,
        int $ttl,
        int $maxUses
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->ttl = $ttl;
        $this->maxUses = $maxUses;
        $this->channel = new Channel($size);
    }

    public function createClient(): WrapperClient
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
        return new WrapperClient($client);
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
        if (!$wrapper->isReusable() || $this->channel->isFull()) {
            $wrapper->client->close();
        } else {
            $this->channel->push($wrapper);
        }
    }
}
