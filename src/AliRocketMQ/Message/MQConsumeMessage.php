<?php

declare(strict_types=1);

/**
 * @author Hi Developer
 * @date   2021-08-19
 */

namespace App\Lib\AliRocketMQ\Message;


use App\Exception\AppException;
use App\Lib\AliRocketMQ\MQConstants;

class MQConsumeMessage
{


    public string $errorCode = '';
    public string $errorMsg = '';

    public string $messageId = '';
    public string $messageBodyMD5 = '';
    public string $messageBody = '';
    public string $messageTag = '';
    public string $receiptHandle = '';


    protected int $publishTime = 0;
    protected int $nextConsumeTime = 0;
    protected int $firstConsumeTime = 0;
    protected int $consumedTimes = 0;
    protected array $properties = [];


    public static function load(string $xml)
    {
        $obj = simplexml_load_string($xml);
        if (!$obj) {
            throw new AppException("解析错误");
        }

        $arr = [];

        foreach ($obj->Message as $v) {
            $cls = new MQConsumeMessage();
            $cls->parse($v);
            $arr[] = $cls;
        }

        return $arr;

    }

    public static function loadPushResponse(string $xml)
    {
        $obj = simplexml_load_string($xml);
        if (!$obj) {
            throw new AppException("解析错误");
        }
        $cls = new MQConsumeMessage();
        $cls->messageId = (string)$obj->MessageId;
        return $cls;
    }

    public function getMessageBody(): string
    {
        return $this->messageBody;
    }

    /**
     * @return mixed
     */
    public function getPublishTime(): int
    {
        return $this->publishTime;
    }

    /**
     * @return mixed
     */
    public function getNextConsumeTime(): int
    {
        return $this->nextConsumeTime;
    }

    /**
     * @return mixed
     */
    public function getFirstConsumeTime(): int
    {
        return $this->firstConsumeTime;
    }

    /**
     * @return mixed
     */
    public function getConsumedTimes(): int
    {
        return $this->consumedTimes;
    }

    public function getProperty($key)
    {
        return $this->properties[$key] ?? null;
    }

    /**
     * 消息KEY
     */
    public function getMessageKey(): string
    {
        return $this->getProperty(MQConstants::MESSAGE_PROPERTIES_MSG_KEY);
    }

    /**
     * 定时消息时间戳，单位毫秒（ms
     */
    public function getStartDeliverTime(): int
    {
        $temp = $this->getProperty(MQConstants::MESSAGE_PROPERTIES_TIMER_KEY);
        if ($temp === null) {
            return 0;
        }
        return (int)$temp;
    }

    /**
     * 事务消息第一次消息回查的最快时间，单位秒
     */
    public function getTransCheckImmunityTime(): int
    {
        $temp = $this->getProperty(MQConstants::MESSAGE_PROPERTIES_TRANS_CHECK_KEY);
        if ($temp === null) {
            return 0;
        }
        return (int)$temp;
    }


    public function parse(object $obj)
    {

        $this->messageId = (string)$obj->MessageId;
        $this->messageBodyMD5 = (string)$obj->MessageBodyMD5;
        $this->messageBody = (string)$obj->MessageBody;
        $this->receiptHandle = (string)$obj->ReceiptHandle;
        $this->publishTime = (int)$obj->PublishTime;
        $this->firstConsumeTime = (int)$obj->FirstConsumeTime;
        $this->nextConsumeTime = (int)$obj->NextConsumeTime;
        $this->consumedTimes = (int)$obj->ConsumedTimes;

        if ($obj->Properties) {
            $arr = explode("|", (string)$obj->Properties);
            foreach ($arr as $v) {
                if ($v) {
                    $tmp = explode(":", $v, 2);
                    $this->properties[$tmp[0]] = $tmp[1];
                }
            }
        }

    }
}
