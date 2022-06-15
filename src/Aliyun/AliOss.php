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
     * @return string 文件内容
     * @throws NetworkException
     */
    public function get(string $filename, string $bucket = ''): string
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

        return $r['body'];
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
}
