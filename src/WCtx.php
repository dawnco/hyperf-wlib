<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2021-07-08
 */

namespace WLib;

use Hyperf\Context\Context;

class WCtx
{

    public static function start(): void
    {
        Context::set("requestId", WUtil::uuid());
    }

    public static function setRequestId(string $requestId): string
    {
        Context::set('requestId', $requestId);
        return $requestId;
    }


    public static function requestId(): string
    {
        return Context::get("requestId") ?: '';
    }

}
