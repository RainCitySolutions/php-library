<?php
declare(strict_types=1);
namespace RainCity\Logging;


use PHPUnit\Framework\TestCase;
use RainCity\TestHelper\StubLogger;
use RainCity\TestHelper\ReflectionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * Note: We don't extend RainCityTestCase because we need to control the
 * logger for this test.
 */
#[CoversClass(\RainCity\Logging\Logger::class)]
#[CoversMethod(\RainCity\Logging\BaseLogger::class, 'getLogger')]
#[CoversMethod(\RainCity\Logging\BaseLogger::class, 'getLoggerObject')]
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
        self::assertNotNull($logger);

        self::assertEquals(BaseLogger::class, self::getLoggerClazz());
    }

    public function testGetLogger_overrideLoggerClass () {
        Logger::setLogger(StubLogger::class);

        $logger = Logger::getLogger('TestStubLogger');
        self::assertNotNull($logger);

        self::assertEquals(StubLogger::class, self::getLoggerClazz());
    }
}
