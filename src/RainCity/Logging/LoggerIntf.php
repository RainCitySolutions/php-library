<?php namespace RainCity\Logging;

use Psr\Log\LoggerInterface;

interface LoggerIntf {
    /**
     * Fetches a logger which supports the LoggerInterface.
     *
     * @param string $loggerName A name for the logger object. Multple calls
     *      to getLogger() with the same logger name will return the same
     *      logger instance.
     * @param string $loggerKey An optional key to be used in grouping
     *      loggers. If not provided, loggers will be grouped by the class
     *      implementing LoggerIntf.
     *
     * @return LoggerInterface A logger
     */
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface;
}
