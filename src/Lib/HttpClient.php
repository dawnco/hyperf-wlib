<?php

declare(strict_types=1);

/**
 * @date   2021-07-10
 */

namespace WLib\Lib;

use Swoole\Coroutine\Http\Client;

class HttpClient
{
    private string $url = '';
    private array $setting = [
        'timeout' => 3,
    ];
    private string $host = '';
    private string $method = 'GET';
    private mixed $data = '';
    private array $headers = [
        // 'Content-Type' => 'application/x-www-form-urlencoded', // form 方式提交
        //'Content-Type'=> 'multipart/form-data', // 上传文件 方式提交
        //'Content-Type'=> 'application/json', // json格式 方式提交
    ];
    private array $cookies = [];
    private int $port = 443;
    private string $path = '';
    private string $query = '';
    private bool $ssl = true;
    private array $fileData = [];
    private array $filePath = [];

    private array $responseHeaders = [];
    private array $responseCookies = [];
    private string $responseBody = '';
    private int $responseStatus = 0;

    /**
     * HttpClient constructor.
     * @param string $url
     * @param array  $setting
     */
    public function __construct(string $url = '', array $setting = [])
    {
        if ($url) {
            $this->setUrl($url);
        }
        foreach ($setting as $k => $v) {
            $this->setting[$k] = $v;
        }
    }

    public function setUrl($url)
    {
        $this->url = $url;
        $info = parse_url($this->url);
        $scheme = $info['scheme'] ?? '';
        $this->ssl = $scheme == 'https' ? true : false;
        $this->host = $info['host'] ?? '';
        $this->port = isset($info['port']) ? intval($info['port']) : ($this->ssl ? 443 : 80);
        $this->path = $info['path'] ?? '/';
        $this->query = $info['query'] ?? "";
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @param array|string $data
     */
    public function setData($data)
    {
        $this->method = "POST";

        if (is_array($data)) {
            $this->headers['Content-Type'] = "application/x-www-form-urlencoded";
        } elseif (is_string($data)) {
            $this->headers['Content-Type'] = "application/json";
        }

        $this->data = $data;
    }

    /**
     * @param array $headers [ 'Content-Type' => 'application/json,]
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param array|int[] $setting
     */
    public function setSetting(array $setting): void
    {
        $this->setting = $setting;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @param array $cookies
     */
    public function setCookies(array $cookies): void
    {
        $this->cookies = $cookies;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @param bool $ssl
     */
    public function setSsl(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    public function addFileData(string $data, string $name, string $mimeType = null, string $filename = null)
    {
        $this->method = "POST";
        $this->fileData[] = ['data' => $data, 'name' => $name];
    }

    public function addFilePath(
        string $path,
        string $name,
        string $mimeType = null,
        string $filename = null,
        int $offset = 0,
        int $length = 0
    ) {
        $this->method = "POST";
        $this->filePath[] = ['path' => $path, 'name' => $name];
    }

    /**
     * @return array
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * @return array
     */
    public function getResponseCookies(): array
    {
        return $this->responseCookies;
    }

    /**
     * @return string
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function getRequestHeader(): array
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getResponseStatus(): int
    {
        return $this->responseStatus;
    }

    public function execute(): bool
    {

        $client = new Client($this->host, $this->port, $this->ssl);

        $client->setData($this->data);
        $client->setMethod($this->method);

        if ($this->cookies) {
            $client->setCookies($this->cookies);
        }

        $client->set($this->setting);

        foreach ($this->fileData as $file) {
            $client->addData($file['data'], $file['name']);
        }

        foreach ($this->filePath as $file) {
            $client->addFile($file['path'], $file['name']);
        }

        if ($this->headers) {
            if ($this->fileData || $this->filePath) {
                unset($this->headers['Content-Type']);
            }
            $client->setHeaders($this->headers);
        }

        $path = $this->path . ($this->query ? ("?" . $this->query) : "");
        $result = $client->execute($path);
        $this->responseStatus = (int)$client->getStatusCode();

        if ($result) {
            $this->responseBody = $client->getBody();
            $this->responseHeaders = $client->getHeaders();
            $this->responseCookies = $client->getCookies() ?: [];
        }

        $client->close();

        return $result;
    }

}
