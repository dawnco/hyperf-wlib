<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-07-01
 */

namespace WLib\Exception;

use WLib\Constant\ErrorCode;
use Throwable;

class AppException extends \Exception
{
    public function __construct(
        string $message = '',
        int $code = ErrorCode::SYSTEM_ERROR,
        Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
