<?php declare(strict_types=1);
namespace RainCity\TestHelper;

use PHPUnit\Framework\MockObject\MockBuilder;
use Psr\Log\LoggerInterface;
use RainCity\Logging\LoggerIntf;

class StubLogger implements LoggerIntf {
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface {
        $obj = new class() extends \PHPUnit\Framework\TestCase {
            public function __construct() {
                // blank ctor to avoid calling TestCase __construct
            }
        };

        return (new MockBuilder($obj, \Psr\Log\LoggerInterface::class))->getMock();
    }
}
