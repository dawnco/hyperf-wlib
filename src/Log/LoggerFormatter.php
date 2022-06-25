<?php

declare(strict_types=1);

/**
 * 阿里云
 * 示例
 * ```
 * [2022-06-25 11:12:17] [service-template] [cat] [Info] [1e3ca8af-34cf-f09a-8e41-bde5353edca5] [1656126737923]
 * ["rmsg"]
 * ```
 * 正则
 * ```
 * \[(\d+-\d+-\d+\s\d+:\d+:\d+)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\] \[([a-zA-Z0-9\-_]*)\]
 * \[([a-zA-Z0-9\-_]*)\] \[([0-9]+)\] (.*)
 * ```
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

        // 来自自定义的
        $wlog = $context['WLOG'] ?? false;

        $requestId = (isset($context['requestId'])
                      && $context['requestId']) ? $context['requestId'] : WCtx::requestId();

        $category = (isset($context['category'])
                     && $context['category']) ? $context['category'] : "";

        $tag = (isset($context['tag'])
                && $context['tag']) ? $context['tag'] : '';

        if ($wlog) {
            $data = $context['message'] ?? '';
        } else {
            $category = 'system';
            $tag = $record->level->name;
            $data['message'] = $message;
            if ($record->extra) {
                $data['extra'] = $record->extra;
            }
            if ($context) {
                $data['context'] = $context;
            }

        }

        if ($this->allowInlineLineBreaks) {
            $msg = print_r($data, true);
        } else {
            $msg = is_string($data) ? $data : app_json_encode($data);
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
