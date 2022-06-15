<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2021-08-19
 */

namespace WLib\Test;

use WLib\AliRocketMQ\MQClient;
use WLib\WConfig;

class MQTest
{
    public function test()
    {
        $config = WConfig::get('aliyun.mq');
        $config['waitSeconds'] = 3;
        $config['topic'] = 'test';
        $config['group'] = 'GID_test';
        $mq = new MQClient($config);
        $message = "xxx " . rand(10, 20);
        $mq->publish($message);
        echo "publish msg " . $message;
        echo PHP_EOL;

        $mq->subscribe(function ($msg) {
            echo "subscribe msg " . $msg;
            echo PHP_EOL;
        });


    }

    public function status()
    {

        $config = Config::get('aliyun.mq');

        $config['waitSeconds'] = 3;
        $config['topic'] = 'test';
        $config['group'] = 'GID_test';

        $mq = new MQHttpClient($config);
        var_dump($mq->consumerStatus($config['group']));
    }
}
