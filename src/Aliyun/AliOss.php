<?php

declare(strict_types=1);

namespace WLib\Aliyun;

use App\Exception\AppException;
use App\Exception\NetworkException;
use Exception;

class AliOss extends Aliyun
{
    /**
     * 保存到oss.
     * @param string $filename 路径  比如  /20210101/file/best.jpg
     * @param string $data     文件内容
     * @param string $bucket
     * @return string[] 保存的url
     * @throws NetworkException
     */
    public function put(string $filename, string $data, string $bucket = ''): array
    {
        if (!$bucket) {
            $bucket = $this->config['bucket'];
        }
        if (!$bucket) {
            throw new AppException('没指定 OSS bucket');
        }

        // https://help.aliyun.com/document_detail/31955.html

        $date = gmdate('D, d M Y H:i:s T');
        $contentType = $this->mime($filename);

        $md5 = base64_encode(md5($data, true));
        $header = [
            'Content-Length' => strlen($data),
            'Host' => $bucket . '.' . $this->config['endpoint'],
            'Content-MD5' => $md5,
            'Content-Type' => $contentType,
            'Date' => $date,
        ];

        $canonicalizeResource = '/' . $bucket . $filename;

        $header['Authorization'] = $this->authorization($header, 'OSS', 'PUT', $canonicalizeResource, ['x-oss']);

        $url = "https://{$bucket}.{$this->config['endpoint']}" . $filename;

        $this->request($url, 'PUT', $header, $data);

        return [
            'url' => $url,
        ];
    }

    /**
     * 获取文件内容.
     * @param string $filename 路径比如 /20210101/file/best.jpg
     * @param string $bucket
     * @return array ["body"=>"文件内容"]
     * @throws NetworkException
     */
    public function get(string $filename, string $bucket = ''): array
    {
        if (!$bucket) {
            $bucket = $this->config['bucket'];
        }
        if (!$bucket) {
            throw new AppException('没指定 OSS bucket');
        }

        $date = gmdate('D, d M Y H:i:s T');

        $header = [
            'Host' => $bucket . '.' . $this->config['endpoint'],
            'Content-MD5' => '',
            'Content-Type' => '',
            'Date' => $date,
        ];

        $canonicalizeResource = '/' . $bucket . $filename;

        $header['Authorization'] = $this->authorization($header, 'OSS', 'GET', $canonicalizeResource, ['x-oss']);

        $url = "https://{$bucket}.{$this->config['endpoint']}" . $filename;

        $r = $this->request($url, 'GET', $header);

        return [
            "body" => $r['body'],
        ];
    }

    public function exist(string $filename, string $bucket = ''): bool
    {

        $bucket = $bucket ?: $this->config['bucket'];

        if (!$bucket) {
            throw new AppException('没指定 OSS bucket');
        }

        $date = gmdate('D, d M Y H:i:s T');

        $header = [
            'Host' => $bucket . '.' . $this->config['endpoint'],
            'Content-MD5' => '',
            'Content-Type' => '',
            'Date' => $date,
        ];

        $canonicalizeResource = '/' . $bucket . $filename;

        $header['Authorization'] = $this->authorization($header, 'OSS', 'HEAD', $canonicalizeResource, ['x-oss']);

        $url = "https://{$bucket}.{$this->config['endpoint']}" . $filename;

        try {
            $this->request($url, 'HEAD', $header);
            return true;
        } catch (Exception $e) {
            return false;
        }

    }


    /**
     * 获取携带签名url
     * @param string $filename 路径 /image/1.jpg
     * @param string $bucket   file-storage
     * @param int    $expires  到期的时间戳
     * @return string[]
     * @throws AppException
     */
    public function getUrlAuthorization(string $filename, int $expires, string $bucket = ''): array
    {
        if (!$bucket) {
            $bucket = $this->config['bucket'];
        }
        if (!$bucket) {
            throw new AppException('没指定 OSS bucket');
        }
        $header = [
            'Host' => $bucket . '.' . $this->config['endpoint'],
            'Content-MD5' => '',
            'Content-Type' => '',
        ];

        $canonicalizeResource = '/' . $bucket . '/' . $filename;

        $signature = $this->urlAuthorization(
            $header,
            'OSS',
            'GET',
            $filename,
            $expires,
            $canonicalizeResource,
            ['x-oss']
        );

        $url = "https://" . $bucket . '.' . $this->config['endpoint'] . '/' . $filename;

        $responseUri = $url . '?' .
                       'OSSAccessKeyId=' . $this->config['accessId'] .
                       '&Expires=' . $expires .
                       '&Signature=' . $signature;

        return [
            'url' => $responseUri
        ];
    }


    protected function urlAuthorization(
        array $header,
        string $type,
        string $method,
        string $filename,
        int $expires,
        string $canonicalizeResource,
        array $canonicalizeHeaders = []
    ): ?string {

        if (isset($header['Date'])) {
            unset($header['Date']);
        }

        if ($expires < time()) {
            throw new AppException('Expiration time cannot be less than current time');
        }

        if ($method == 'GET') {
            $header['Content-MD5'] = '';
            $header['Content-Type'] = '';
        }

        $canonicalizeHeaders = $this->canonicalizeHeaders($header, $canonicalizeHeaders);

        $str = $method . "\n";
        $str .= ($header['Content-MD5'] ?? '') . "\n";
        $str .= ($header['Content-Type'] ?? '') . "\n";
        $str .= $expires . "\n";
        $str .= $canonicalizeHeaders;
        $str .= $canonicalizeResource;

        return urlencode(base64_encode(hash_hmac('sha1', $str, $this->config['secretKey'], true)));
    }

}
