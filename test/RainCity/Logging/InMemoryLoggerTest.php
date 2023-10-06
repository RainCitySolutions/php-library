<?php declare(strict_types=1);
namespace RainCity\Logging;


use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \RainCity\Logging\InMemoryLogger
 *
 */
class InMemoryLoggerTest extends TestCase
{
    public function testGetLogger()
    {
        $logger = new InMemoryLogger();
        
        $this->assertNotNull($logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testGetLogMsgs_emptyArray()
    {
        $logger = new InMemoryLogger();
        
        $this->assertEmpty($logger->getLogMsgs());
    }

    public function testGetLogMsgs_withMsgs()
    {
        $logger = new InMemoryLogger();
        
        $logger->critical('Info Msg1');
        $logger->critical('Info Msg2');
        
        $msgs = $logger->getLogMsgs();
        
        $this->assertNotEmpty($msgs);
        $this->assertCount(2, $msgs);
    }
}
