<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool\Imp;

class PoolCnf
{
    // 连接池大小
    public static int $size = 20;
    // 连接的最大空闲时间,秒
    public static int $ttl = 60;
    // 每个连接最大请求次数
    public static int $maxUses = 10000;

}
