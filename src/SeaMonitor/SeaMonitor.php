<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-15
 */

namespace WLib\SeaMonitor;

use WLib\SeaMonitor\Pool\MessageConnection;
use WLib\SeaMonitor\Pool\MessagePool;
use Hyperf\Utils\ApplicationContext;
use WLib\WLog;

class SeaMonitor
{
    /**
     * 发送数据
     * @param mixed $data 数据 可以被 json_encode 的数据
     * @return void
     */
    public static function push(mixed $data): void
    {
        go(function () use ($data) {
            $pool = ApplicationContext::getContainer()->get(MessagePool::class);
            $connection = null;
            try {
                /**
                 * @var MessageConnection $connection
                 */
                $connection = $pool->get();
                $connection->send(json_encode($data));
            } catch (\Throwable $e) {
                WLog::error("wupd-push-error:" . $e->getMessage());
            } finally {
                if ($connection) {
                    $pool->release($connection);
                }
            }
        });
    }

}
