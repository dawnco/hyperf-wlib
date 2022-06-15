<?php

declare(strict_types=1);

/**
 * @author Hi Developer
 * @date   2021-08-18
 */

namespace App\Lib\AliRocketMQ\Message;

use App\Lib\AliRocketMQ\MQConstants;
use App\Lib\AliRocketMQ\MQException;
use XMLWriter;

class MQProducerMessage
{

    public string $messageId = '';
    public string $messageBodyMD5 = '';
    public string $messageBody = '';
    public string $messageTag = '';
    public string $messageKey = '';
    public array $properties = [];


    public function __construct(string $messageBody)
    {
        $this->messageBody = $messageBody;
    }

    public function setMessageTag($messageTag)
    {
        $this->messageTag = $messageTag;
    }

    public function putProperty(string $key, string $value)
    {
        if ($key === "" || $value === "") {
            return;
        }
        $this->properties[$key] = $value;
    }


    /**
     * 设置消息KEY，如果没有设置，则消息的KEY为RequestId
     * @param $key
     */
    public function setMessageKey(string $key)
    {
        $this->putProperty(MQConstants::MESSAGE_PROPERTIES_MSG_KEY, $key);
    }

    /**
     * 定时消息，单位毫秒（ms），在指定时间戳（当前时间之后）进行投递。
     * 如果被设置成当前时间戳之前的某个时刻，消息将立刻投递给消费者
     * @param $timeInMillis
     */
    public function setStartDeliverTime(int $timeInMillis)
    {
        $this->putProperty(MQConstants::MESSAGE_PROPERTIES_TIMER_KEY, (string)$timeInMillis);
    }

    public function toXml()
    {
        $xmlWriter = new XMLWriter;
        $xmlWriter->openMemory();
        $xmlWriter->startDocument("1.0", "UTF-8");
        $xmlWriter->startElementNS(null, "Message", MQConstants::XML_NAMESPACE);


        if ($this->messageBody) {
            $xmlWriter->writeElement(MQConstants::MESSAGE_BODY, $this->messageBody);
        }
        if ($this->messageTag) {
            $xmlWriter->writeElement(MQConstants::MESSAGE_TAG, $this->messageTag);
        }

        if ($this->properties) {
            $this->checkPropValid();
            $xmlWriter->writeElement(MQConstants::MESSAGE_PROPERTIES,
                implode("|", array_map(function ($v, $k) {
                    return $k . ":" . $v;
                }, $this->properties, array_keys($this->properties))));
        }

        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $xmlWriter->outputMemory();

    }

    private function checkPropValid()
    {
        foreach ($this->properties as $key => $value) {
            if ($key === "" || $value === "") {
                throw new MQException("Message Properties is null or empty");
            }

            if ($this->isContainSpecialChar($key) || $this->isContainSpecialChar($value)) {
                throw new MQException("Message's property can't contains: & \" ' < > : |");
            }
        }
    }

    private function isContainSpecialChar($str)
    {
        return strpos($str, "&") !== false
               || strpos($str, "\"") !== false
               || strpos($str, "'") !== false
               || strpos($str, "<") !== false
               || strpos($str, ">") !== false
               || strpos($str, ":") !== false
               || strpos($str, "|") !== false;
    }
}
