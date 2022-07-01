<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-24
 */

namespace WLib;

use App\Exception\AppException;
use WLib\Constant\ErrorCode;
use WLib\Exception\NetworkException;

class WRpc
{
    /**
     * @throws AppException
     * @throws \Throwable
     */
    public static function call(callable $call)
    {
        try {
            return $call();
        } catch (\Hyperf\RpcClient\Exception\RequestException $e) {
            if ($e->getThrowableCode() == ErrorCode::NETWORK_ERROR) {
                // 网络请求失败了
                throw new NetworkException($e->getThrowableMessage() ?: $e->getMessage(), ErrorCode::NETWORK_ERROR, $e);
            } else {
                throw new \WLib\Exception\AppException($e->getThrowableMessage() ?: $e->getMessage(),
                    $e->getThrowableCode() ?: ErrorCode::SYSTEM_ERROR, $e);
            }
        } catch (\Hyperf\LoadBalancer\Exception\RuntimeException $e) {
            // 没有服务可用
            throw new NetworkException($e->getMessage(), ErrorCode::NETWORK_ERROR, $e);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            // 网络连接失败
            throw new NetworkException($e->getMessage(), ErrorCode::NETWORK_ERROR, $e);
        } catch (\Throwable $e) {
            throw new AppException($e->getMessage(), ErrorCode::SYSTEM_ERROR, $e);
        }
    }
}
