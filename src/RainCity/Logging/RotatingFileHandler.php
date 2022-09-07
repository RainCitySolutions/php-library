<?php namespace RainCity\Logging;

class RotatingFileHandler extends \Monolog\Handler\RotatingFileHandler
{
    /**
     * @return string The handler's filename
     */
    public function getLogFilename(): string {
        return $this->filename;
    }

    /**
     *
     * @return string
     */
    public function getFilenameGlobPattern(): string
    {
        return parent::getGlobPattern();
    }
}
