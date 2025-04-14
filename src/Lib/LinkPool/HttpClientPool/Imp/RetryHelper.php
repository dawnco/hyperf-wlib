<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2025-04-12
 */

namespace WLib\Lib\LinkPool\HttpClientPool\Imp;


class RetryHelper
{
    public static function withRetry(callable $fn, int $maxRetries = 2): ?HttpResponse
    {
        $attempts = 0;

        while ($attempts <= $maxRetries) {

            /**
             * @var $response HttpResponse
             */
            $response = $fn();

            if ($response && $response->errCode === 0 && $response->statusCode > -1) {
                return $response;
            }

            $attempts++;
            if ($attempts >= $maxRetries) {
                return $response;
            }

            \Swoole\Coroutine::sleep(0.05);
        }

        return null;
    }
}
