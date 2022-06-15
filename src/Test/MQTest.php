<?php

declare(strict_types=1);

/**
 * @author Hi Developer
 * @date   2021-08-19
 */

namespace App\Lib\AliRocketMQ;


use App\Lib\Util;
use App\Loan\Core\Config;

class MQTest
{
    public static function test()
    {
        $config = Config::get('aliyun.mq');

        $config['waitSeconds'] = 3;
        $config['topic'] = 'calc-push-msg';
        $config['group'] = 'GID_calc-push-msg';

        $mq = new MQClient($config);
        $mq->publish('1');
        $mq->publish('2');
        $mq->publish('3', 3000);
        $mq->subscribe(function ($v) {
            echo Util::date();
            echo " ";
            echo $v;
            echo "\n";
        });
    }

    public static function status()
    {

        $config = Config::get('aliyun.mq');

        $config['waitSeconds'] = 3;
        $config['topic'] = 'calc-push-msg';
        $config['group'] = 'GID_calc-push-msg';

        $mq = new MQHttpClient($config);
        app_dump($mq->consumerStatus($config['group']));
    }
}
