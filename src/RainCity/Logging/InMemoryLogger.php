<?php
namespace RainCity\Logging;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Processor\PsrLogMessageProcessor;

class InMemoryLogger extends Logger
{
    private const LOGGER_NAME = 'InMemoryLogger';

    public function __construct()
    {
        parent::__construct(self::LOGGER_NAME);

        $this->setupLogger($this);
    }

    protected function setupLogger(Logger $logger)
    {
        $dateformat = 'M d H:i:s';
        $format = '%datetime% %level_name% %channel%: %message% %context%'.PHP_EOL;

        $formatter = new LineFormatter ($format, $dateformat, false, true);

        $logHandler = new TestHandler();
        $logHandler->setFormatter($formatter); //  attach the formatter to the handler

        $this->pushHandler($logHandler); // push the handler to Monolog

        $this->pushProcessor(new PsrLogMessageProcessor(null, true));
    }

    public function getLogMsgs(): array
    {
        $result = array();

        $handler = $this->handlers[array_key_first($this->handlers)];

        foreach ($handler->getRecords() as $rcd) {
            $result[] = $rcd->formatted;
        }

        return $result;
    }
}
