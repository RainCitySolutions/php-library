<?php declare(strict_types=1);
namespace RainCity\TestHelper;

use PHPUnit\Framework\TestResult;
use Psr\Log\LoggerInterface;
use RainCity\Logging\LoggerIntf;

class StubLogger implements LoggerIntf {
    public static function getLogger(string $loggerName, ?string $loggerKey = null): LoggerInterface {
        $obj = new class() extends \PHPUnit\Framework\TestCase {
            public function provides()
            {}

            public function count()
            {}

            public function toString()
            {}

            public function run(TestResult $result = null)
            {}

            public function requires()
            {}
            public function sortId()
            {}

        };
        return $obj->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
    }
}
