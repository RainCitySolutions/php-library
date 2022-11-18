<?php declare(strict_types=1);
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
     *  @var array
     */
    private static $loggerImpl = array();

    /**
     * {@inheritDoc}
     * @see \RainCity\Logging\LoggerIntf
     */
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface {
        $loggerKey ?: get_called_class();   // if $loggerKey isn't set, use the called class as the key

        if (!isset(self::$loggerImpl[$loggerKey])) {
            $calledClassName = get_called_class();
            self::$loggerImpl[$loggerKey] = new $calledClassName();
        }

        return self::$loggerImpl[$loggerKey]->getLoggerObject($loggerName);
    }


    /************************************************************************
     * Class variables
     ***********************************************************************/

    /** @var array Array of logger objects. */
    private $loggers = array();
    private $logLevel = 500;

    /**
     * Initializes class instance.
     *
     * Setups up the base logger to serve as the pattern for other loggers.
     */
    protected function __construct() {
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
    protected function setupLogger(\Monolog\Logger $logger) {
        $dateformat = "M d H:i:s";
        $format = "%datetime% %level_name% %channel% (%extra.userId%/%extra.userName%): %message% %context% %extra%".PHP_EOL;

        $formatter = new \Monolog\Formatter\LineFormatter ($format, $dateformat, false, true);

        $handler = new RotatingFileHandler($this->getLogFile(), 14, $this->getLogLevel());
        $handler->setFormatter($formatter); //  attach the formatter to the handler

        $logger->pushHandler($handler); // push the logger to Monolog

        $logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor(null, true));
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
    protected function getLoggerObject(string $loggerName): LoggerInterface {
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
    protected function getLogFile (): string {
        $logFile = sys_get_temp_dir().'/logs/application.log';

        if (!file_exists(dirname($logFile)) ) {
            mkdir(dirname($logFile), 0660, true);
        }

        return $logFile;
    }

    protected function getLogLevel() {
        return $this->logLevel;
    }

    protected function setLogLevel($level) {
        $this->logLevel = $level;
    }
}
