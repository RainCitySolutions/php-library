<?php
declare(strict_types=1);
namespace RainCity\Logging;

/**
 * Helper methods for logging and debugging.
 *
 */
class Helper
{
    /**
     * Dumps a variable to a string.
     *
     * @param $var mixed The variable to dump
     *
     * @return string A string containing the dumped variable
     */
    public static function dumpVar(mixed $var): string
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }

    /**
     * Write a log message using error_log().
     *
     * @param mixed $message A string message or object to log.
     * @param array<mixed> $data Any additoinal data to log.
     *
     * @return bool Returns true on success, false on failure.
     */
    public static function log($message, array $data = array()): bool
    {
        $result = true;

        if (is_string($message)) {
            if (empty($data)) {
                $result = error_log($message);
            } else {
                $result = error_log($message.PHP_EOL.print_r($data, true));
            }
        } else {
            $result = error_log(print_r($message, true));
        }

        return $result;
    }

    /**
     * Logs the stacktrace using \RainCity\Logging\Logger at info level using
     * the logger for the calling class.
     *
     * @see \RainCity\Logging\Logger
     */
    public static function logStackTrace(): void
    {
        Logger::getLogger(get_called_class())->info('back trace: ', debug_backtrace ());
    }
}
