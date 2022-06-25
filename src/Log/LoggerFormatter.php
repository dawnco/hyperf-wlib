<?php

declare(strict_types=1);

/**
 * @author Dawnc
 * @date   2022-06-20
 */

namespace WLib\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;
use WLib\WConfig;
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
        $serviceName = WConfig::get('app_name');
        $message = $record->message;
        $context = $record->context;

        $requestId = (isset($context['requestId'])
                      && $context['requestId']) ? $context['requestId'] : WCtx::requestId();

        $category = (isset($context['category'])
                     && $context['category']) ? $context['category'] : 'system';

        $tag = (isset($context['tag'])
                && $context['tag']) ? $context['tag'] :  $record->level->name;


        unset($context['requestId'], $context['category'], $context['tag'], $context['tag']);


        if (isset($context['WLOG'])) {
            $data = $message;
        } else {
            $data['message'] = $message;
            $data['extra'] = $record->extra;
            $data['context'] = $context;
        }


        $msg = app_json_encode($data);
        if ($this->allowInlineLineBreaks) {
            $msg = print_r($data, true);
        }

        return sprintf("[%s] [%s] [%s] [%s] [%s] [%s] %s\n",
            $date,
            $serviceName,
            $category,
            $tag,
            $requestId,
            $time,
            $msg);
    }
}
