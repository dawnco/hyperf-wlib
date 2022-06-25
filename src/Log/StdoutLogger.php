<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-25
 */

namespace WLib\Log;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use WLib\WConfig;
use WLib\WCtx;
use WLib\WUtil;

class StdoutLogger implements StdoutLoggerInterface
{

    public static function get(string $name = '')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name ?: WConfig::get('app_name'));
    }

    public function emergency(\Stringable|string $message, array $context = [])
    {
        self::get()->emergency($message, $context);
    }

    public function alert(\Stringable|string $message, array $context = [])
    {
        self::get()->alert($message, $context);
    }

    public function critical(\Stringable|string $message, array $context = [])
    {
        self::get()->critical($message, $context);
    }

    public function error(\Stringable|string $message, array $context = [])
    {
        self::get()->error($message, $context);
    }

    public function warning(\Stringable|string $message, array $context = [])
    {
        self::get()->warning($message, $context);
    }

    public function notice(\Stringable|string $message, array $context = [])
    {
        self::get()->notice($message, $context);
    }

    public function info(\Stringable|string $message, array $context = [])
    {
        self::get()->info($message, $context);
    }

    public function debug(\Stringable|string $message, array $context = [])
    {
        self::get()->debug($message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = [])
    {
        self::get()->log($level, $message, $context);
    }
    
}
