<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-15
 */

namespace WLib;

use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Hyperf\Redis\Redis;

class WRedis
{
    /**
     * @param string $poolName
     * @return Redis
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function connection(string $poolName = 'default'): Redis
    {
        return ApplicationContext::getContainer()->get(RedisFactory::class)->get($poolName);
    }

}
