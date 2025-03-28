<?php
declare(strict_types=1);
namespace RainCity\Logging;

use Psr\Log\LoggerInterface;


/**
 * Base class for managing a set of named loggers.
 *
 * Provides the base implemenation for loggers and maintains seperate of
 * loggers for each super class. Unless overridden writes to
 * logs/application.log in the system temporary directory.
 */
class BaseLogger implements LoggerIntf
{
    public const BASE_LOGGER = 'base';
    /**
     * An array of loggers that are, or extend BaseLogger. One logger is
     * allowed for each class.
     *
     *  @var array<BaseLogger>
     */
    private static array $loggerImpl = array();

    /**
     * {@inheritDoc}
     * @see \RainCity\Logging\LoggerIntf
     */
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface {
        if (is_null($loggerKey)) {
            $loggerKey = get_called_class();   // if $loggerKey isn't set, use the called class as the key
        }

        if (!isset(self::$loggerImpl[$loggerKey])) {
            $calledClassName = get_called_class();
            self::$loggerImpl[$loggerKey] = new $calledClassName();
        }

        return self::$loggerImpl[$loggerKey]->getLoggerObject($loggerName);
    }


    /************************************************************************
     * Class variables
     ***********************************************************************/

    /** @var array<\Monolog\Logger> Array of logger objects. */
    private array $loggers = [];
    private int $logLevel = 500;

    /**
     * Initializes class instance.
     *
     * Setups up the base logger to serve as the pattern for other loggers.
     */
    protected function __construct()
    {
        $logger = new \Monolog\Logger(self::BASE_LOGGER); // create an initial logger

        $this->setupLogger($logger);

        $this->loggers[self::BASE_LOGGER] = $logger;
    }


    /**
     * Setup the logger provided with a rotating file handler and a
     * PsrLogMessageProcessor.
     *
     * @param \Monolog\Logger $logger
     */
    protected function setupLogger(\Monolog\Logger $logger): void
    {
        $formatter = new \Monolog\Formatter\LineFormatter (
            $this->getLogMsgFormat(),
            $this->getLogDateFormat(),
            false,
            true
            );

        $handler = new RotatingFileHandler($this->getLogFile(), 14, $this->getLogLevel());
        $handler->setFormatter($formatter); //  attach the formatter to the handler

        $logger->pushHandler($handler); // push the logger to Monolog

        $logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor(null, true));
    }

    /**
     * Fetch the date format for log messages.
     *
     * @return string A date format string.
     */
    protected function getLogDateFormat(): string
    {
        return 'M d H:i:s';
    }

    /**
     * Fetch the format string for log messages.
     *
     * @return string The format string.
     */
    protected function getLogMsgFormat(): string
    {
        return join(' ', [
            '%datetime%',
            '%level_name%',
            '%channel%',
            ':',
            '%message% %context% %extra%'
        ])
        .PHP_EOL;
    }

    /**
     * Retrieves a logger associated with the name provided from the list
     * of loggers.
     *
     * If a logger doesn't exist, one is created using the base logger as
     * a template.
     *
     * @param  string $loggerName The name associated with a logger.
     *
     * @return LoggerInterface
     */
    protected function getLoggerObject(string $loggerName): LoggerInterface
    {
        if (isset($this->loggers[$loggerName])) {
            $logger = $this->loggers[$loggerName];
        }
        else {
            // clone the base logger
            $baselogger = $this->loggers[self::BASE_LOGGER];
            $logger = $baselogger->withName($loggerName);
            $this->loggers[$loggerName] = $logger;
        }

        return $logger;
    }

    /**
     * Returns the name of the log file to be used.
     *
     * @return string
     */
    protected function getLogFile (): string
    {
        $logFile = sys_get_temp_dir().'/logs/application.log';

        if (!file_exists(dirname($logFile)) ) {
            mkdir(dirname($logFile), 0660, true);
        }

        return $logFile;
    }

    protected function getLogLevel(): int|string|\Monolog\Level
    {
        return $this->logLevel;
    }

    protected function setLogLevel(int $level): void
    {
        $this->logLevel = $level;
    }
}
