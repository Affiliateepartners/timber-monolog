<?php

namespace Liteweb\TimberMonolog;

class TimberFormatter extends \Monolog\Formatter\JsonFormatter
{
    public function __construct(int $batchMode = self::BATCH_MODE_NEWLINES, bool $appendNewline = false)
    {
        parent::__construct($batchMode, $appendNewline);
    }

    public function format(array $record): string
    {
        $record = $this->setup($record);

        $record = isset($record['context']['TAPI_do_format']) ? $this->reformatTimberApi($record) : $this->formatCustomContext($record);

        $record = $this->cleanup($record);

        return parent::format([$record]);
    }

    private function setup(array $record): array
    {
        $record['$schema'] = 'https://raw.githubusercontent.com/timberio/log-event-json-schema/v4.0.1/schema.json';
        $record['event']   = $record['event']   ?? [];
        $record['context'] = $record['context'] ?? [];

        $record['level']   = strtolower($record['level_name']);

        $record['level'] === 'warning' and $record['level'] = 'warn'; // I have to...

        $record['dt']      = \Carbon\Carbon::instance($record['datetime'])->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z');

        return $record;
    }

    private function reformatTimberApi(array $record): array
    {
        $event   = $record['context']['TAPI_event'] ?? [];
        $context = $record['context']['TAPI_context'] ?? [];

        unset($record['context']);

        $record['event']   = $event;
        $record['context'] = $context;

        return $record;
    }

    private function formatCustomContext(array $record): array
    {
        if(!empty($record['context']))
        {
            $type = $record['context']['name'] ?? uniqid();
            $data = $record['context']['data'] ?? $record['context'];

            unset($record['context']);

            $record['context']['custom'][$type] = $data;
        }

        if(!empty($record['event']))
        {
            $type = $record['event']['name'] ?? uniqid();
            $data = $record['event']['data'] ?? $record['event'];

            unset($record['event']);

            $record['event']['custom'][$type] = $data;
        }

        return $record;
    }

    private function cleanup(array $record): array
    {
        if(empty($record['event']))   unset($record['event']);
        if(empty($record['context'])) unset($record['context']);

        unset($record['TAPI_do_format']);
        unset($record['TAPI_event']);
        unset($record['TAPI_context']);
        unset($record['level_name']);
        unset($record['datetime']);
        unset($record['extra']);
        unset($record['channel']);

        return $record;
    }
}