<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-25
 */

namespace WLib\Log;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use WLib\WConfig;


class StdoutLogger
{
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(LoggerFactory::class)->get(WConfig::get('app_name'));
    }

    public static function get()
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get(WConfig::get('app_name'));
    }
}
