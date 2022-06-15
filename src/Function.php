<?php

declare(strict_types=1);

use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;

function app_json_encode($var): false|string
{
    return json_encode($var, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
}

function app_json_decode($str, $associative = true): mixed
{
    return json_decode($str, $associative);
}

function arr_get($arr, $index, $default = null): mixed
{
    return Arr::get($arr, $index, $default);
}
