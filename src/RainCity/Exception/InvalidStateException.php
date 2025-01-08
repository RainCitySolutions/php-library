<?php
declare(strict_types = 1);
namespace RainCity\Exception;

class InvalidStateException extends \Exception
{
    /**
     * {@inheritDoc}
     * @see \Exception::__construct()
     */
    public function __construct(string $message = '""', int $code = null, \Throwable $previous = null)
    {
        parent::__construct('Invalid State: ' . $message, $code, $previous);
    }
}
