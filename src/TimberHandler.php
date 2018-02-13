<?php

namespace Liteweb\TimberMonolog;

class TimberHandler extends \Monolog\Handler\AbstractProcessingHandler
{
    private $api;

    function __construct(\Liteweb\TimberApi\TimberApi $api, $level = \Monolog\Logger::DEBUG, bool $bubble = true)
    {
        $this->api = $api;
    }

    protected function getDefaultFormatter(): \Monolog\Formatter\FormatterInterface
    {
        return new TimberFormatter();
    }

    protected function write(array $record)
    {
        $response = $this->api->sendJsonLogLine($record['formatted']);
    }
}