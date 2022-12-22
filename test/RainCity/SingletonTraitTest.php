<?php declare(strict_types=1);
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

/**
 * @covers \RainCity\SingletonTrait
 *
 */
class SingletonTraitTest extends RainCityTestCase
{
    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        ReflectionHelper::setClassProperty(TestSingletonTrait::class, 'instance', null, true);
    }

    public function testClone () {
        $this->expectError();
        $obj = TestSingletonTrait::instance();
        $obj->__clone();
    }

    public function testWakeup () {
        $this->expectError();
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

