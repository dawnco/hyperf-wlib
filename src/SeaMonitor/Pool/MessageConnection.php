<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-15
 */

namespace WLib\SeaMonitor\Pool;

use Hyperf\Contract\ConnectionInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Client;
use WLib\WConfig;

class MessageConnection implements ConnectionInterface
{

    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(SWOOLE_SOCK_UDP);
        $this->connect();
    }

    public function getConnection()
    {

    }

    public function connect()
    {
        $host = WConfig::get('sea_monitor_push.host', '172.21.67.61');
        $port = (int)WConfig::get('sea_monitor_push.port', 9799);

        if ($this->client->isConnected()) {
            return;
        }

        if ($this->client->connect($host, $port)) {
            $this->client->close();
            $this->client->connect($host, $port);
        }
    }

    public function reconnect(): bool
    {
        $this->connect();
    }

    public function check(): bool
    {
        return $this->client->connected;
    }

    public function close(): bool
    {
        $this->client->close();
        return true;
    }

    public function release(): void
    {

    }

    public function send(string $data): int
    {
        $this->connect();
        return $this->client->send($data) ?: 0;
    }


    public function read()
    {
        return $this->client->recv(3) ?: '';
    }

}
