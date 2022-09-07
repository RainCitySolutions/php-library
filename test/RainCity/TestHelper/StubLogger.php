<?php
namespace RainCity\TestHelper;

use Psr\Log\LoggerInterface;
use RainCity\Logging\LoggerIntf;

class StubLogger implements LoggerIntf {
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface {
        $obj = new class() extends \PHPUnit\Framework\TestCase {};
        return $obj->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
    }
}
