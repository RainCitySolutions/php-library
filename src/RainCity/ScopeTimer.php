<?php declare(strict_types=1);
namespace RainCity;

/**
 * A convienence class to time a period of execution. The timer starts when
 * an instance of the class is created and ends when the instance goes out
 * of scope.
 *
 * When using it is necessary to assign a new instance of the class to a
 * variable that will determine the scope.
 *      e.g. $myTimer = new ScopeTimer($logger, "log message %s");
 *
 * If no variable is used the instance will go out of scope immediately after
 * creation resulting in no time.
 *      e.g. new ScopeTimer($logger, "log message %s");
 *
 * The log message string can contain a "%s" which will be replaced with the
 * execution time.
 *
 */
class ScopeTimer {
    private $logger;
    private $msg;
    private $timer;

    /**
     * Log a timer message when the instance goes out of scope.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $msg
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, $msg) {
        $this->logger = $logger;
        $this->msg = $msg;
        $this->timer = new Timer(true);
    }

    public function __destruct() {
        $timeStr = $this->timer->getTime();

        $logMsg = sprintf($this->msg, $timeStr);
        $this->logger->info($logMsg);
    }
}
