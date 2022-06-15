<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2021-08-18
 */

namespace WLib\AliRocketMQ;

use Closure;
use Swoole\Coroutine\Http\Client;
use WLib\AliRocketMQ\Message\MQConsumeMessage;
use WLib\AliRocketMQ\Message\MQProducerMessage;
use WLib\Lib\HttpClient;
use WLib\WUtil;

class MQHttpClient
{
    private Client $client;
    private string $endpoint = '';
    private string $accessId = '';
    private string $secretKey = '';
    private string $securityToken = '';
    private string $agent = '';
    private string $instanceId = '';


    public function __construct(array $config)
    {

        $this->accessId = (string)arr_get($config, 'accessId');
        $this->secretKey = (string)arr_get($config, 'secretKey');
        $this->endpoint = (string)arr_get($config, 'endpoint');
        $this->securityToken = (string)arr_get($config, 'securityToken', '');
        $this->instanceId = (string)arr_get($config, 'instanceId', '');

        $this->agent = "mq-swoole/1.0.0(PHP " . PHP_VERSION . ")";
        $this->client = new Client($this->endpoint, 80, false);

    }

    public function send(MQProducerMessage $message, string $topic, string $instanceId = '')
    {

        $qs = sprintf("ns=%s", $instanceId ?: $this->instanceId);
        $path = sprintf("/topics/%s/messages", $topic);
        $fullPath = sprintf("%s?%s", $path, $qs);

        $body = $message->toXml();

        $header = [
            'Host' => $this->endpoint,
            'Date' => gmdate(MQConstants::GMT_DATE_FORMAT),
            MQConstants::USER_AGENT => $this->agent,
            MQConstants::CONTENT_LENGTH => strlen($body),
            MQConstants::CONTENT_TYPE => 'text/xml',
            MQConstants::VERSION_HEADER => MQConstants::VERSION_VALUE,
            //'Content-MD5'               => base64_encode(md5($body, true)),
        ];

        if ($this->securityToken) {
            $header[MQConstants::SECURITY_TOKEN] = $this->securityToken;
        }

        $method = 'POST';


        $sign = $this->signature($header, '', $method, $path, [], $qs);
        $header[MQConstants::AUTHORIZATION] = MQConstants::AUTH_PREFIX . " " . $this->accessId . ":" . $sign;


        $this->client->setData($body);
        $this->client->setMethod($method);
        $this->client->setHeaders($header);
        $this->client->execute($fullPath);
        $this->client->close();

        $statusCode = $this->client->getStatusCode();
        if ($statusCode != 201) {
            throw new MQException("推送消息失败", $this->client->body, $statusCode);
        }

        return MQConsumeMessage::loadPushResponse($this->client->body);

    }

    public function pull(
        Closure $closure,
        string $topic,
        string $consumer,
        string $tag = '',
        array $option = [],
        string $instanceId = ''
    ) {

        $qs = sprintf("ns=%s&consumer=%s&numOfMessages=%s&waitseconds=%s",
            $instanceId ?: $this->instanceId,
            $consumer,
            arr_get($option, 'numOfMessages', 3),
            arr_get($option, 'waitSeconds', 1),
        );

        if ($tag) {
            $qs .= "&tag=$tag";
        }

        $path = sprintf("/topics/%s/messages", $topic,);
        $fullPath = sprintf("%s?%s", $path, $qs);

        $header = [
            'Host' => $this->endpoint,
            'Date' => gmdate(MQConstants::GMT_DATE_FORMAT),
            MQConstants::USER_AGENT => $this->agent,
            MQConstants::CONTENT_LENGTH => 0,
            MQConstants::CONTENT_TYPE => 'text/xml',
            MQConstants::VERSION_HEADER => MQConstants::VERSION_VALUE,
        ];

        $method = 'GET';

        $sign = $this->signature($header, '', $method, $path, [], $qs);
        $header[MQConstants::AUTHORIZATION] = MQConstants::AUTH_PREFIX . " " . $this->accessId . ":" . $sign;
        $this->client->setMethod($method);
        $this->client->setHeaders($header);
        $this->client->execute($fullPath);
        $this->client->close();

        if ($this->client->getStatusCode() == 200) {
            $closure(MQConsumeMessage::load($this->client->body));
        } else {
            throw new MQException('消费失败', $this->client->body);
        }

    }

    public function ack(array $receiptHandle, string $topic, string $consumer, string $instanceId = ''): bool
    {

        $qs = sprintf("ns=%s&consumer=%s",
            $instanceId ?: $this->instanceId,
            $consumer,
        );

        $path = sprintf("/topics/%s/messages",
            $topic,
        );
        $fullPath = sprintf("%s?%s", $path, $qs);

        $ids = [];
        foreach ($receiptHandle as $v) {
            $ids[] = sprintf('<ReceiptHandle>%s</ReceiptHandle>', $v);
        }

        $body =
            sprintf('<?xml version="1.0" encoding="UTF-8"?><ReceiptHandles xmlns="http://mq.aliyuncs.com/doc/v1/">%s</ReceiptHandles>',
                implode("", $ids));

        $header = [
            'Host' => $this->endpoint,
            'Date' => gmdate(MQConstants::GMT_DATE_FORMAT),
            MQConstants::USER_AGENT => $this->agent,
            MQConstants::CONTENT_LENGTH => strlen($body),
            MQConstants::CONTENT_TYPE => 'text/xml',
            MQConstants::VERSION_HEADER => MQConstants::VERSION_VALUE,
        ];

        $method = 'DELETE';

        $sign = $this->signature($header, '', $method, $path, [], $qs);
        $header[MQConstants::AUTHORIZATION] = MQConstants::AUTH_PREFIX . " " . $this->accessId . ":" . $sign;

        $this->client->setData($body);
        $this->client->setMethod($method);
        $this->client->setHeaders($header);

        $this->client->execute($fullPath);
        $this->client->close();

        $status = $this->client->getStatusCode();
        if ($status == 204) {
            return true;
        }
        throw new MQException("ACK 失败", $this->client->body);
    }

    protected function signature(
        array $header,
        string $type,
        string $method,
        string $resource,
        array $canonicalizeHeaders = [],
        string $queryString = ''
    ) {

        $contentMd5 = "";
        if (isset($header['Content-MD5'])) {
            $contentMd5 = $header['Content-MD5'];
        }
        $contentType = "";
        if (isset($header['Content-Type'])) {
            $contentType = $header['Content-Type'];
        }
        $date = $header['Date'];
        $canonicalizedResource = $resource;
        if ($queryString != null) {
            $canonicalizedResource .= "?" . $queryString;
        }
        if (0 !== strpos($canonicalizedResource, "/")) {
            $canonicalizedResource = "/" . $canonicalizedResource;
        }


        $tmpHeaders = array();
        foreach ($header as $key => $value) {
            if (0 === strpos($key, MQConstants::HEADER_PREFIX)) {
                $tmpHeaders[$key] = $value;
            }
        }
        ksort($tmpHeaders);

        $canonicalizedHeaders = implode("\n", array_map(function ($v, $k) {
            return $k . ":" . $v;
        }, $tmpHeaders, array_keys($tmpHeaders)));

        $stringToSign =
            strtoupper($method) . "\n" . $contentMd5 . "\n" . $contentType . "\n" . $date . "\n" . $canonicalizedHeaders
            . "\n" . $canonicalizedResource;

        return base64_encode(hash_hmac("sha1", $stringToSign, $this->secretKey, true));
    }


    /**
     * 队列状态
     * 文档  https://help.aliyun.com/document_detail/29600.html
     * @param string $group
     * @param string $instanceId
     */
    public function consumerStatus(string $group, string $instanceId = '')
    {

        $tmp = explode(".", $this->endpoint);
        $regionId = $tmp[2];

        $param['Action'] = 'OnsConsumerStatus';
        $param['GroupId'] = $group;
        $param['InstanceId'] = $instanceId ?: $this->instanceId;
        $param['Detail'] = "true";

        // 公共
        $param['Format'] = 'JSON';
        $param['productName'] = 'Ons';
        $param['domain'] = sprintf("ons.%s.aliyuncs.com", $regionId);
        $param['Version'] = '2019-02-14';
        $param['AccessKeyId'] = $this->accessId;
        //$param['AccessKeySecret'] = $this->secretKey;
        $param['Timestamp'] = date('Y-m-d\\TH:i:s\\Z', time() - date('Z'));


        $param['SignatureVersion'] = "1.0";
        $param['SignatureMethod'] = "HMAC-SHA1";
        $param['SignatureNonce'] = WUtil::uuid();


        $url = sprintf("http://ons.%s.aliyuncs.com/", $regionId);


        ksort($param);

        $tmp = [];
        foreach ($param as $k => $v) {
            $tmp[] = rawurlencode($k) . "=" . rawurlencode($v);
        }


        $stringToSign = "GET" . "&" . rawurlencode("/") . "&" . rawurlencode(implode("&", $tmp));


        $signature = base64_encode(hash_hmac("sha1", $stringToSign, $this->secretKey . "&", true));
        $param['Signature'] = $signature;

        $url .= "?" . http_build_query($param, "", "&", PHP_QUERY_RFC3986);


        $client = new HttpClient($url);


        $client->setMethod('GET');
        $client->execute();

        if ($client->getResponseStatus() == 200) {
            $json = app_json_decode($client->getResponseBody());
            return [
                'consumeTps' => arr_get($json['Data'], 'ConsumeTps', 0), // 总消费TPS。
                'delay' => arr_get($json['Data'], 'DelayTime', 0), // 延迟时间。
                'lastTimestamp' => arr_get($json['Data'], 'LastTimestamp', 0), // 最后消费时间。
                'totalDiff' => arr_get($json['Data'], 'TotalDiff', 0), // 集群总的消费堆积。
            ];
        }


    }


    public function close()
    {
        $this->client->close();
    }
}
