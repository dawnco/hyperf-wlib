<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool;

use WLib\Lib\LinkPool\HttpClientPool\Imp\HttpResponse;
use WLib\Lib\LinkPool\HttpClientPool\Imp\PoolManager;
use WLib\Lib\LinkPool\HttpClientPool\Imp\RetryHelper;

class HttpClientPool
{
    public static function get(string $url, array $options = []): ?HttpResponse
    {
        return self::request('GET', $url, $options);
    }

    /**
     * @param string $url
     * @param string $data
     * @param array  $options ["header" => "每次需要单独设置 否则可能被其他请求使用", 在失效前  header是 一直保留的]
     * @return HttpResponse|null
     */
    public static function post(string $url, string $data, array $options = []): ?HttpResponse
    {
        $options['data'] = $data;
        return self::request('POST', $url, $options);
    }

    private static function request(string $method, string $url, array $options = []): ?HttpResponse
    {
        $parsed = parse_url($url);
        $host = $parsed['host'];
        $port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);
        $ssl = $parsed['scheme'] === 'https';
        $path = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');

        // 从连接池获取连接
        $pool = PoolManager::getPool($host, $port, $ssl);

        return RetryHelper::withRetry(function () use ($method, $pool, $host, $port, $path, $options) {
            $wrapper = $pool->get();
            // 设置请求头
            $defaultHeaders = [
                'Host' => $host,
                'User-Agent' => 'HttpClientPool/1.0',
            ];
            $wrapper->client->setHeaders(array_merge($defaultHeaders, $options['header'] ?? []));
            if ($method === 'POST') {
                $wrapper->client->post($path, $options['data']);
            } else {
                $wrapper->client->setData("");  // 处理客户端GET复用的问题
                $wrapper->client->get($path);
            }

            $response = new HttpResponse($wrapper->client);
            $pool->put($wrapper);
            return $response;
        });


    }
}
