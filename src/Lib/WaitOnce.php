<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-11-16
 */

namespace WLib\Lib;

use Swoole\Coroutine;
use WLib\WRedis;
use WLib\WUtil;

/**
 * 使用redis 解决并发请求问题
 */
class WaitOnce
{
    private \Hyperf\Redis\Redis $redis;

    private string $lockKey = "";

    /**
     * lockKey 过期时间 默认 8 秒
     * @var int 单位秒
     */
    private int $lockExpire = 8;

    /**
     * @param string $lockKey
     * @param string $redisPoolName
     */
    public function __construct(string $lockKey, string $redisPoolName = 'default')
    {
        $this->redis = WRedis::connection($redisPoolName);
        $this->setLockKey($lockKey);
    }

    /**
     * 设置锁过期时间
     * @param int $lockExpire
     * @return void
     */
    public function setLockExpire(int $lockExpire): void
    {
        $this->lockExpire = $lockExpire;
    }

    /**
     * 设置锁 KEY
     * @param string $lockKey
     * @return void
     */
    public function setLockKey(string $lockKey): void
    {
        $this->lockKey = "LOCK:ONCE:$lockKey";
    }

    /**
     * 相同的 $lockKey 在 $lockExpire 只会执行一次 $call 其他的等待结果或者超时返回 false
     * @param callable $call
     * @param int      $waitTime 等待的毫秒 默认 8000 毫秒
     * @return mixed
     */
    public function wait(callable $call, int $waitTime = 8000): mixed
    {
        $dataKey = $this->lockKey . ":data";

        // 有数据直接返回
        $cache = $this->redis->get($dataKey);
        if ($cache) {
            return json_decode($cache, true);
        }

        if ($this->lock()) {
            // 获取锁
            $ret = $call();
            $this->redis->setex(
                $dataKey,
                $this->lockExpire,
                json_encode($ret)
            );
            return $ret;
        } else {
            $start = WUtil::milliseconds();
            while (true) {
                if (WUtil::milliseconds() - $start > $waitTime) {
                    return false;
                }
                $cache = $this->redis->get($dataKey);
                if ($cache) {
                    return json_decode($cache);
                }
                Coroutine::sleep(0.02);
            }
        }
    }

    /**
     * 获取锁的执行 $call 并返回结果, 未获取的返回null
     * @param callable $call
     * @return mixed
     */
    public function once(callable $call): mixed
    {
        if ($this->lock()) {
            try {
                return $call();
            } finally {
                $this->unlock();
            }
        } else {
            return null;
        }
    }


    /** 获取锁 获取成功返回true 否则返回false
     * @return bool
     */
    public function lock(): bool
    {
        $ok = $this->redis->set($this->lockKey, 1, ['nx', 'ex' => $this->lockExpire]);
        return (bool)$ok;
    }

    /**
     * 解锁
     * @return void
     */
    public function unlock(): void
    {
        $this->redis->del($this->lockKey);
    }

}
