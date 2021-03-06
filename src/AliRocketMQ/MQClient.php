<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2021-08-19
 */

namespace WLib\AliRocketMQ;


use Closure;
use Throwable;
use WLib\AliRocketMQ\Message\MQProducerMessage;
use WLib\WLog;

class MQClient
{
    protected array $config = [
        'accessId' => '',
        'secretKey' => '',
        'endpoint' => '',
        'instanceId' => '',
        'topic' => '',
        'group' => '',
        'numOfMessages' => 3,
        'waitSeconds' => 1,
    ];

    /**
     * @var MQHttpClient
     */
    protected null|MQHttpClient $client;

    public function __construct(array $config)
    {
        foreach ($config as $k => $v) {
            $this->config[$k] = $v;
        }
        $this->client = new MQHttpClient($config);
    }


    /**
     * @param string $message
     * @param int    执行的时间戳 毫秒
     * @return string 消息ID
     */
    public function samplePublish(string $data, int $delayAt = 0): string
    {
        $message = new MQProducerMessage($data);
        if ($delayAt) {
            $message->setStartDeliverTime($delayAt);
        }
        return $this->publish($message);
    }

    /**
     * @param MQProducerMessage $message
     * @return string 消息ID
     */
    public function publish(MQProducerMessage $message): string
    {
        $obj = $this->client->send($message, $this->config['topic'], $this->config['instanceId']);
        return $obj->messageId;
    }

    /**
     * 订阅队列
     * @param Closure $closure 抛出异常 则不会确认消费了
     * @param string  $tag     标签
     */
    public function subscribe(Closure $closure, string $tag = '')
    {
        $option = [
            'numOfMessages' => $this->config['numOfMessages'],
            'waitSeconds' => $this->config['waitSeconds'],
        ];

        while (true) {
            try {
                $this->client->pull(function (array $msg) use ($closure) {
                    $this->callback($closure, $msg);
                }, $this->config['topic'], $this->config['group'], $tag,
                    $option, $this->config['instanceId']);
            } catch (Throwable $e) {
                WLog::debug("订阅失败 $tag " . $e->getMessage());
            }
            sleep($this->config['waitSeconds']);
        }
    }

    private function callback(Closure $closure, $msg)
    {
        $receiptHandle = [];
        foreach ($msg as $v) {
            try {
                WLog::debug("订阅成功  " . $v->messageId . " " . $v->messageTag);
                $closure($v->messageBody, $v);
                $receiptHandle[] = $v->receiptHandle;
            } catch (Throwable $e) {
                WLog::debug('处理消息异常 ' . $e->getMessage());
            }
        }
        if ($receiptHandle) {
            try {
                $this->client->ack($receiptHandle, $this->config['topic'], $this->config['group'],
                    $this->config['instanceId']);
            } catch (Throwable $e) {
                WLog::error(sprintf("ACK 失败 %s", $e->getMessage()));
            }
        }

    }

    public function close(): void
    {
        $this->client->close();
    }

    public function __destruct()
    {
        $this->client->close();
        $this->client = null;
    }
}
