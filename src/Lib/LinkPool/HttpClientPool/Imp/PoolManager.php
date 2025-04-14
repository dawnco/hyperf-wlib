<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool\Imp;

class PoolManager
{
    private static array $pools = [];

    public static function getPool(string $host, int $port, bool $ssl): ConnectionPool
    {
        $key = "$host:$port:" . ($ssl ? '1' : '0');
        if (!isset(self::$pools[$key])) {

            $size = PoolCnf::$size;
            $ttl = PoolCnf::$ttl;
            $maxUses = PoolCnf::$maxUses;

            self::$pools[$key] = new ConnectionPool(
                $host,
                $port,
                $ssl,
                $size,
                $ttl,
                $maxUses,
            );
        }
        return self::$pools[$key];
    }
}
