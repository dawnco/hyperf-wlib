<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

$dir = env('LOG_DIR');
$logLevel = \Monolog\Level::fromName(env('LOG_LEVEL', 'INFO'));
if ($dir) {
    // 输出到文件
    return [
        'default' => [
            'handler' => [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    'filename' => $dir . "/server.log",
                    'level' => $logLevel
                ],
            ],
            'formatter' => [
                'class' => \WLib\Log\LoggerFormatter::class,
                'constructor' => [
                    //  [北京时间] [一级分类] [二级分类] [requestId] [time] msg
                    'format' => "",
                    'dateFormat' => '',
                    'allowInlineLineBreaks' => false,
                ],
            ],
        ],
    ];
} else {
    // 输出到控制台
    return [
        'default' => [
            'handler' => [
                'class' => \Monolog\Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => 'php://stdout',
                    'level' => $logLevel
                ],
            ],
            'formatter' => [
                'class' => \WLib\Log\LoggerFormatter::class,
                'constructor' => [
                    //  [北京时间] [一级分类] [二级分类] [requestId] [time] msg
                    'format' => "",
                    'dateFormat' => '',
                    'allowInlineLineBreaks' => true,
                ],
            ],
        ],
    ];
}
