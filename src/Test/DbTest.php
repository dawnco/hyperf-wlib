<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-15
 */

namespace WLib\Test;

use WLib\WDb;

class DbTest
{
    public function test()
    {
        $val = WDb::getData("SELECT id,sn FROM error");
        var_dump($val);

    }
}
