<?php namespace RainCity\Logging;

use Psr\Log\LoggerInterface;

/**
 * Wrapper class for managing loggers using a specified logger class.
 *
 * By default will use the FileSystemLogger. A different logger can be used
 * by calling the setLogger method with a class that support the HsirLogger
 * interface.
 */
final class Logger implements LoggerIntf
{
    /** @var Logger */
    private static $loggerClazz = BaseLogger::class;

    /**
     * Prevent class construction
     */
    private function __construct() {}

    /**
     * Sets the Logger class to be used for logging.
     *
     * @param string $loggerClazz The class to use for logging, e.g. MyLogger::class
     */
    public static function setLogger(string $loggerClazz) {
        self::$loggerClazz = $loggerClazz;
    }

    /**
     * Returns Logger object associated with the specified name.
     *
     * @param string $loggerName Name associated with a logger object.
     *
     * @return LoggerInterface Object assocaited with the name provided.
     */
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface
    {
        return self::$loggerClazz::getLogger($loggerName, $loggerKey);
    }
}
