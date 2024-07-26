<?php declare(strict_types=1);
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(\RainCity\Singleton::class)]
#[CoversMethod(\RainCity\Logging\Logger::class, 'getLogger')]
class SingletonTest extends RainCityTestCase
{
    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        ReflectionHelper::setClassProperty(TestSingleton::class, 'instance', array(), true);
    }

    public function testClone () {
        $this->expectExceptionMessage('__clone should not be called on singleton class');
        $obj = TestSingleton::instance();
        $obj->__clone();
    }

    public function testWakeup () {
        $this->expectExceptionMessage('__wakeup should not be called on singleton class');
        $obj = TestSingleton::instance();
        $obj->__wakeup();
    }

    public function testInstance() {
        $obj1 = TestSingleton::instance();
        self::assertNotNull($obj1);

        $obj2 = TestSingleton::instance();
        self::assertNotNull($obj2);

        self::assertEquals($obj1, $obj2);
    }

    public function testGeInstance_noInstance() {
        $obj = TestSingleton::getInstance(TestSingleton::class);

        self::assertNull($obj);
    }

    public function testGeInstance_instance() {
        $obj1 = TestSingleton::instance();
        self::assertNotNull($obj1);

        $obj2 = TestSingleton::getInstance(TestSingleton::class);
        self::assertNotNull($obj2);

        self::assertEquals($obj1, $obj2);
    }
}

class TestSingleton extends Singleton {}
