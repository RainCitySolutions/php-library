<?php declare(strict_types=1);
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

/**
 * @covers \RainCity\SingletonTrait
 *
 * @covers \RainCity\Singleton::triggerIncorrectUseWarning
 */
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
        $this->assertNotNull($obj1);

        $obj2 = TestSingletonTrait::instance();
        $this->assertNotNull($obj2);

        $this->assertEquals($obj1, $obj2);
    }

    public function testInstance_initializeInstance() {
        $obj = TestSingletonTrait::instance();
        $this->assertNotNull($obj);
        $this->assertTrue($obj->initInstCalled);
    }

    public function testInstance_extendedClass() {
        $obj1 = TestExtendedNonSingleton::instance();
        $this->assertNotNull($obj1);

        $obj2 = TestExtendedNonSingleton::instance();
        $this->assertNotNull($obj2);

        $this->assertEquals($obj1, $obj2);
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

