<?php
declare(strict_types=1);
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(\RainCity\TestSingletonTrait::class)]
#[CoversMethod(\RainCity\Singleton::class, 'triggerIncorrectUseWarning')]
class SingletonTraitTest extends RainCityTestCase
{
    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        ReflectionHelper::setClassProperty(TestSingletonTrait::class, 'instance', null, true);
    }

    public function testClone () {
        $this->expectExceptionMessage('__clone should not be called on singleton class');
        $obj = TestSingletonTrait::instance();
        $obj->__clone();
    }

    public function testWakeup () {
        $this->expectExceptionMessage('__wakeup should not be called on singleton class');
        $obj = TestSingletonTrait::instance();
        $obj->__wakeup();
    }

    public function testInstance() {
        $obj1 = TestSingletonTrait::instance();
        self::assertNotNull($obj1);

        $obj2 = TestSingletonTrait::instance();
        self::assertNotNull($obj2);

        self::assertEquals($obj1, $obj2);
    }

    public function testInstance_initializeInstance() {
        $obj = TestSingletonTrait::instance();
        self::assertNotNull($obj);
        self::assertTrue($obj->initInstCalled);
    }

    public function testInstance_extendedClass() {
        $obj1 = TestExtendedNonSingleton::instance();
        self::assertNotNull($obj1);

        $obj2 = TestExtendedNonSingleton::instance();
        self::assertNotNull($obj2);

        self::assertEquals($obj1, $obj2);
    }
}

class TestNonSingleton {}

class TestSingletonTrait {
    use SingletonTrait;

    public $initInstCalled = false;

    protected function initializeInstance() {
        $this->initInstCalled = true;
    }
}

class TestExtendedNonSingleton
    extends TestNonSingleton
{
    use SingletonTrait;
}

