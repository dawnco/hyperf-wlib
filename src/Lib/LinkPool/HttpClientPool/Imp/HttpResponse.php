<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool\Imp;

use Swoole\Coroutine\Http\Client;

class HttpResponse
{
    public int $statusCode;
    public array $headers;
    public string $body;
    public int $errCode;
    public Client $client;

    public function __construct(Client $client)
    {
        $this->statusCode = $client->statusCode;
        $this->headers = $client->headers ?? [];
        $this->body = $client->body;
        $this->errCode = $client->errCode;
        $this->client = $client;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isJson(): bool
    {
        return isset($this->headers['content-type'])
               && stripos($this->headers['content-type'], 'application/json') !== false;
    }

    public function json(): ?array
    {
        if ($this->isJson()) {
            return json_decode($this->body, true);
        }
        return null;
    }
}
