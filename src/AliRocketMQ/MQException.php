<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2021-08-18
 */

namespace WLib\AliRocketMQ;


use WLib\Exception\AppException;
use Throwable;

class MQException extends AppException
{
    public function __construct(string $message, string $xml = '', int $code = 1, Throwable $previous = null)
    {

        if ($xml) {
            $obj = simplexml_load_string($xml);
            $message .= " => " . $obj->Message;
        }




        parent::__construct($message, $code, $previous);
    }
}
