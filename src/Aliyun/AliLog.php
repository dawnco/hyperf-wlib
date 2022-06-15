<?php

declare(strict_types=1);

namespace WLib\Aliyun;

use App\Exception\AppException;
use App\Exception\NetworkException;
use Extend\Ali\Log\Content;
use Extend\Ali\Log\Log;
use Extend\Ali\Log\LogGroup;

class AliLog extends Aliyun
{
    /**
     * 阿里云日志文档 https://help.aliyun.com/document_detail/29026.html.
     * @param            $data
     * @param mixed      $store
     * @param null|mixed $project
     */
    public function put(array $data, string $store, $project = null)
    {
        if (!$project) {
            $project = $this->config['project'];
        }

        if (!$project) {
            throw new AppException('没指定 AliLog project');
        }

        $log = new Log();
        $log->setTime(time());
        $temp = [];
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $content = new Content();
            $content->setKey($k);
            $content->setValue($v);
            $temp[] = $content;
        }

        $log->setContents($temp);

        $logGroup = new LogGroup();
        $logGroup->setLogs([$log]);
        $raw = $logGroup->serializeToString();
        $ret = $this->log($raw, $store, $project);

        return $ret['header']['x-log-requestid'];
    }

    /**
     * @param string $query
     * @param array  $opt ['from' => '开始时间戳', 'to'=> '结束时间戳', 'size'=> '结果数量', 'offset'=>'开始位置']
     * @param string $store
     * @param null   $project
     * @return array
     * @throws AppException
     * @throws NetworkException
     */
    public function gets(string $query = '', array $opt = [], string $store = '', $project = null)
    {

        if (!$project) {
            $project = $this->config['project'];
        }

        if (!$project) {
            throw new AppException('没指定 AliLog project');
        }


        $queryString['from'] = arr_get($opt, 'from', strtotime(date("Y-m-d")));
        $queryString['line'] = arr_get($opt, 'size', 50);
        $queryString['offset'] = arr_get($opt, 'offset', 0);
        $queryString['query'] = $query;
        $queryString['reverse'] = 'true';
        $queryString['to'] = arr_get($opt, 'to', strtotime(date("Y-m-d 23:59:59")));

        $topic = arr_get($opt, 'topic', '');
        if ($topic) {
            $queryString['topic'] = $topic;
        }

        $queryString['type'] = 'log';

        ksort($queryString);
        $tmp = [];
        foreach ($queryString as $k => $v) {
            $tmp[] = "$k=$v";
        }

        $resource = "/logstores/{$store}?" . implode("&", $tmp);

        $header['Date'] = gmdate('D, d M Y H:i:s T');
        $header['Content-Length'] = 0;
        $header['x-log-bodyrawsize'] = 0;
        $header['Content-Type'] = 'application/json';
        $header['Host'] = $project . '.' . $this->config['endpoint'];
        $header['x-log-apiversion'] = '0.6.0';
        $header['x-log-signaturemethod'] = 'hmac-sha1';

        $header['Authorization'] = $this->authorization($header, 'LOG', 'GET', $resource, ['x-log', 'x-acs']);

        $url = "https://{$project}.{$this->config['endpoint']}/logstores/{$store}?" . http_build_query($queryString);

        $ret = $this->request($url, 'GET', $header);

        return [
            'total' => arr_get($ret, 'header.x-log-count'),
            'data' => $ret['body'] ? app_json_decode($ret['body']) : [],
        ];

    }


    private function log($data, $store, $project = null)
    {
        // https://help.aliyun.com/document_detail/29012.html

        $size = strlen($data);

        $header['Date'] = gmdate('D, d M Y H:i:s T');
        $header['Content-Type'] = 'application/x-protobuf';
        $header['Host'] = $project . '.' . $this->config['endpoint'];
        $header['Content-Length'] = $size;
        $header['Content-MD5'] = strtoupper(md5($data));
        $header['x-log-bodyrawsize'] = $size;
        $header['x-log-apiversion'] = '0.6.0';
        $header['x-log-signaturemethod'] = 'hmac-sha1';

        $resource = "/logstores/{$store}/shards/lb";

        $header['Authorization'] = $this->authorization($header, 'LOG', 'POST', $resource, ['x-log', 'x-acs']);

        $url = "https://{$project}.{$this->config['endpoint']}$resource";

        return $this->request($url, 'POST', $header, $data);
    }
}
