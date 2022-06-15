<?php

declare(strict_types=1);

namespace WLib\Aliyun;

use App\Exception\NetworkException;
use Swoole\Coroutine\Http\Client;

class Aliyun
{
    protected array $config = [];

    public function __construct(array $config)
    {
        /*
         * 格式
         * [
         * 'endpoint'  => '',
         * 'accessId'  => '',
         * 'secretKey' => '',
         * ];
         */
        $this->config = $config;
    }

    protected function authorization(
        array $header,
        string $type,
        string $method,
        string $resource,
        $canonicalizeHeaders = []
    ): ?string {
        // LOG 签名 https://help.aliyun.com/document_detail/29012.html
        // OSS 签名 https://help.aliyun.com/document_detail/31950.html

        // ['x-log', 'x-acs']

        $canonicalizeHeaders = $this->canonicalizeHeaders($header, $canonicalizeHeaders);
        $CanonicalizeResource = $resource;

        $str = "{$method}\n";
        $str .= $header['Content-MD5'] . "\n";
        $str .= $header['Content-Type'] . "\n";
        $str .= $header['Date'] . "\n";
        $str .= $canonicalizeHeaders;
        $str .= $CanonicalizeResource;

        $signature = base64_encode(hash_hmac('sha1', $str, $this->config['secretKey'], true));
        return $type . ' ' . $this->config['accessId'] . ':' . $signature;
    }

    protected function request(string $url, string $method, array $header, ?string $data = null)
    {
        $info = parse_url($url);
        $scheme = $info['scheme'] ?? '';
        $ssl = $scheme == 'https' ? true : false;

        $host = $info['host'] ?? '';
        $port = $info['port'] ?? ($ssl ? 443 : 80);
        $path = $info['path'] ?? '/';

        $client = new Client($host, $port, $ssl);
        $client->setHeaders($header);
        if ($data) {
            $client->setData($data);
        }

        $client->setMethod($method);
        $client->execute($path);
        $client->close();

        $code = $client->getStatusCode();
        $body = $client->getBody();
        if ($code != 200) {
            throw new NetworkException(sprintf("OSS 响应失败 client: %s:%s httpStatus: %s 响应: %s", $client->errCode,
                socket_strerror($client->errCode), $code, $body));
        }

        return [
            'header' => $client->getHeaders(),
            'code' => $code,
            'body' => $body,
        ];
    }

    protected function mime($name)
    {
        $index = strrpos($name, '.');

        if ($index !== false) {
            $ext = substr($name, $index + 1);
        } else {
            $ext = '';
        }

        $default = 'application/octet-stream';

        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'json' => 'application/json',
            'gz' => 'application/gzip',
        ];

        return $map[$ext] ?? $default;
    }

    protected function canonicalizeHeaders($header, $prefix = [])
    {
        if (!$prefix) {
            return '';
        }

        $canonicalize = [];
        foreach ($header as $name => $value) {
            $name = strtolower($name);
            $start = substr($name, 0, 5);
            if (in_array($start, $prefix)) {
                $canonicalize[$name] = $value;
            }
        }
        ksort($canonicalize);
        $temp = [];
        foreach ($canonicalize as $k => $v) {
            $temp[] = $k . ':' . $v . "\n";
        }
        return implode('', $temp);
    }
}
