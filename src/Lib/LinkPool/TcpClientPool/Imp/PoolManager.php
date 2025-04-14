<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\TcpClientPool\Imp;

class PoolManager
{
    private static array $pools = [];

    public static function getPool(string $host, int $port): ConnectionPool
    {
        $key = "$host:$port:";
        if (!isset(self::$pools[$key])) {

            $size = PoolCnf::$size;
            $ttl = PoolCnf::$ttl;
            $maxUses = PoolCnf::$maxUses;

            self::$pools[$key] = new ConnectionPool(
                $host,
                $port,
                $size,
                $ttl,
                $maxUses,
            );
        }
        return self::$pools[$key];
    }
}
