<?php

declare(strict_types=1);

namespace WLib\Test;


use WLib\Aliyun\AliLog;
use WLib\Aliyun\AliOss;
use WLib\WConfig;

class AliTest
{
    public function test()
    {
        $config = WConfig::get('aliyun.log');
        $log = new AliLog($config);
        $r = $log->put(
            ['apiMethodName' => 'test', 'apiRequestData' => 'xxx'],
            'app-log',
            'sg-dev-log',
        );

        echo "log id => $r";
        echo PHP_EOL;
        $config = WConfig::get('aliyun.oss');
        $oss = new AliOss($config);
        $r = $oss->put('/test/a1.json', json_encode(['hello' . rand(100, 999)]));
        echo "写OSS  " . json_encode($r);
        echo PHP_EOL;

        $r = $oss->get('/test/a1.json', 'loan-sg-test');
        echo "读OSS  {}" . json_encode($r);
        echo PHP_EOL;

    }
}
