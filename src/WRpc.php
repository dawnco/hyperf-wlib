<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-24
 */

namespace WLib;

use App\Exception\AppException;

class WRpc
{
    /**
     * @throws AppException
     * @throws \Throwable
     */
    public static function call(callable $call, string $rpcName)
    {
        try {
            return $call();
        } catch (\Hyperf\RpcClient\Exception\RequestException $e) {
            $error = "$rpcName Rpc 请求异常 " . $e->getThrowableMessage() . " code: " . $e->getThrowableCode();
            WLog::error($error);
            throw new AppException($error, $e->getThrowableCode() ?: -110, $e);
        }
    }
}
