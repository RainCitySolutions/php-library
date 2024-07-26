<?php declare(strict_types=1);
namespace RainCity\Logging;


use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\RainCity\Logging\InMemoryLogger::class)]
class InMemoryLoggerTest extends TestCase
{
    public function testGetLogger()
    {
        $logger = new InMemoryLogger();
        
        self::assertNotNull($logger);
        self::assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testGetLogMsgs_emptyArray()
    {
        $logger = new InMemoryLogger();
        
        self::assertEmpty($logger->getLogMsgs());
    }

    public function testGetLogMsgs_withMsgs()
    {
        $logger = new InMemoryLogger();
        
        $logger->critical('Info Msg1');
        $logger->critical('Info Msg2');
        
        $msgs = $logger->getLogMsgs();
        
        self::assertNotEmpty($msgs);
        self::assertCount(2, $msgs);
    }
}
