<?php

declare(strict_types=1);

namespace WLib\Test;


use WLib\WConfig;

class AliTest
{
    public static function test()
    {
        $config = WConfig::get('aliyun.log');
        $log = new AliLog($config);
        $r = $log->put(
            ['apiMethodName' => 'test', 'apiRequestData' => 'xxx'],
            'sg-dev-log-store',
            'sg-dev-log',
        );

        $config = Config::get('aliyun.oss');
        $oss = new AliOss($config);
        $r = $oss->put('/test/a1.json', json_encode(['hello' . rand(100, 999)]), 'loan-sg-test');
        Log::debug("写OSS  {$r}");

        $r = $oss->get('/test/a1.json', 'loan-sg-test');
        Log::debug("读OSS  {$r}");
    }
}
