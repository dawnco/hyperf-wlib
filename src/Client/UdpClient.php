<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-03
 */

namespace WLib\Client;

use Swoole\Coroutine;
use Swoole\Coroutine\Client;

class UdpClient
{

    protected mixed $socket = null;
    protected mixed $client = null;

    public function __construct(protected string $host = '', protected int $port = 9580)
    {

    }

    protected function connect(): void
    {
        if (class_exists("\\Swoole\\Coroutine\\Client") && Coroutine::getCid() > 0) {
            $this->initClient();
        } else {
            $this->initSocket();
        }
    }

    protected function initSocket(): void
    {
        if (!$this->socket) {
            $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        }
    }

    protected function initClient(): void
    {
        if (!$this->client) {
            $this->client = new Client(SWOOLE_SOCK_UDP);
            $this->client->connect($this->host, $this->port);
        }
    }

    protected function sendBySocket(string $data): int
    {
        if (!$this->socket) {
            return 0;
        }
        $len = strlen($data);
        $size = socket_sendto($this->socket, $data, $len, 0, $this->host, $this->port);
        return $size ?: 0;
    }

    protected function sendByClient(string $data): int
    {
        $size = $this->client->send($data);
        return $size ?: 0;
    }

    public function send(string $data): int
    {
        $this->connect();
        if ($this->client) {
            return $this->sendByClient($data);
        } else {
            return $this->sendBySocket($data);
        }
    }

    public function close(): void
    {
        if ($this->socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
        if ($this->client) {
            $this->client->close();
            $this->client = null;
        }
    }
}
