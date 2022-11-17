<?php declare(strict_types=1);
namespace RainCity;

use RainCity\Logging\Logger;
use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;
use RainCity\TestHelper\StubLogger;

/**
 * @covers \RainCity\Singleton
 *
 */
class SingletonTest extends RainCityTestCase
{
    private $testSingleton;


    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        Logger::setLogger(StubLogger::class);
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        ReflectionHelper::setClassProperty(TestSingleton::class, 'instance', array(), true);
    }

    public function testClone () {
        $this->expectError();
        $obj = TestSingleton::instance();
        $obj->__clone();
    }

    public function testWakeup () {
        $this->expectError();
        $obj = TestSingleton::instance();
        $obj->__wakeup();
    }

    public function testInstance() {
        $obj1 = TestSingleton::instance();
        $this->assertNotNull($obj1);

        $obj2 = TestSingleton::instance();
        $this->assertNotNull($obj2);

        $this->assertEquals($obj1, $obj2);
    }

    public function testGeInstance_noInstance() {
        $obj = TestSingleton::getInstance(TestSingleton::class);

        $this->assertNull($obj);
    }

    public function testGeInstance_instance() {
        $obj1 = TestSingleton::instance();
        $this->assertNotNull($obj1);

        $obj2 = TestSingleton::getInstance(TestSingleton::class);
        $this->assertNotNull($obj2);

        $this->assertEquals($obj1, $obj2);
    }
}

class TestSingleton extends Singleton {}
