<?php
namespace RainCity\Logging;

use PHPUnit\Framework\TestCase;
use RainCity\TestHelper\StubLogger;
use RainCity\TestHelper\ReflectionHelper;

/**
 * @covers \RainCity\Logging\Logger
 *
 */
class LoggerTest extends TestCase
{
    private static $orgLoggerClass;

    private static function getLoggerClazz() {
        return ReflectionHelper::getClassProperty(Logger::class, 'loggerClazz');
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        self::$orgLoggerClass = self::getLoggerClazz();
    }


    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        ReflectionHelper::setClassProperty(Logger::class, 'loggerClazz', self::$orgLoggerClass);
    }

    public function testGetLogger_defaultLoggerClass () {
        $logger = Logger::getLogger('TestBaseLogger');
        $this->assertNotNull($logger);

        $this->assertEquals(BaseLogger::class, self::getLoggerClazz());
    }

    public function testGetLogger_overrideLoggerClass () {
        Logger::setLogger(StubLogger::class);

        $logger = Logger::getLogger('TestSubLogger');
        $this->assertNotNull($logger);

        $this->assertEquals(StubLogger::class, self::getLoggerClazz());
    }
}
