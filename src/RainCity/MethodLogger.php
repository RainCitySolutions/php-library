<?php declare(strict_types=1);
namespace RainCity;

use RainCity\Logging\Logger;

class MethodLogger
{
    private $method;
    private $timer;


    public function __construct()
    {
        $this->method = $this->getCallingMethodName();
        Logger::getLogger(get_class($this))->debug("Entering {$this->method}");

        $this->timer = new Timer('start');
    }

    function __destruct()
    {
        $this->timer->stop();

        Logger::getLogger(get_class($this))->debug("Exiting {$this->method} after {$this->timer->getTime()}");
    }

    private function getCallingMethodName()
    {
        $trace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2];

        $str = '';
        if (isset($caller['class'])) {
            $str .= $caller['class'];
        }
        if (isset($caller['type'])) {
            $str .= $caller['type'];
        }

        if (isset($caller['function'])) {
            $str .= $caller['function'];
        }

        return $str;
    }
}
