<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-09-15
 */

namespace WLib\SeaMonitor\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;

class MessagePool extends Pool
{
    public function createConnection(): ConnectionInterface
    {
        return new MessageConnection();
    }
}
