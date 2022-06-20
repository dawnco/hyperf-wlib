<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-20
 */

namespace WLib\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;
use WLib\WCtx;
use WLib\WDate;
use WLib\WUtil;

class LoggerFormatter extends LineFormatter
{
    /**
     * @inheritDoc
     */
    public function format(LogRecord $record): string
    {

        $date = WDate::getInstance('cn')->format();
        $time = WUtil::milliseconds();
        $category = $record->channel ?: 'hyperf';
        $tag = $record->level->name ?: 'debug';
        $message = $record->message;

        $data['message'] = $message;
        $data['context'] = $record->context;
        $data['extra'] = $record->extra;
        $requestId = WCtx::requestId();

        return sprintf("[%s] [HH%s] [%s] [%s] [%s] %s\n",
            $date,
            $category,
            $tag,
            $requestId,
            $time,
            app_json_encode($data));
    }
}
