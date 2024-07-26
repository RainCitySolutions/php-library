<?php declare(strict_types=1);
namespace RainCity;

use PHPUnit\Framework\TestCase;
use RainCity\TestHelper\ReflectionHelper;
use PHPUnit\Framework\Attributes\CoversTrait;

#[CoversTrait(\RainCity\SerializeAsArrayTrait::class)]
class SerializeAsArrayTraitTest extends TestCase
{
    const TEST_STR_KEY = 'strValue';
    const TEST_EXTRA_KEY = 'extraValue';
    const TEST_INT_KEY = 'intValue';
    const TEST_OBJ_KEY = 'objValue';
    const TEST_OBJ_VALUE_KEY = 'classValue';
    const TEST_NEW_OBJ_KEY = 'newObjValue';
    
    const TEST_STR_VALUE = 'TestValue';
    const TEST_ALT_STR_VALUE = 'AltTestValue';
    const TEST_INT_VALUE = 85185105;
    const TEST_ALT_INT_VALUE = 50158158;

    private static $serialObjectPrefix;
    private $testObj;
    private $testObjValue;


    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        self::$serialObjectPrefix = sprintf(
            'O:%d:"%s":',
            strlen(TestSerializeAsArrayTrait::class),
            TestSerializeAsArrayTrait::class
            );
    }

    /**
     *
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->testObjValue = (object) array (self::TEST_OBJ_VALUE_KEY => 1);

        $this->testObj = new TestSerializeAsArrayTrait(
            self::TEST_STR_VALUE,
            self::TEST_INT_VALUE,
            $this->testObjValue
            );
    }

    public function testMagicSerialize()
    {
        $array = $this->testObj->__serialize();

        self::assertNotNull($array);

        self::assertArrayHasKey(self::TEST_STR_KEY, $array);
        self::assertEquals(self::TEST_STR_VALUE, $array[self::TEST_STR_KEY]);

        self::assertArrayHasKey(self::TEST_INT_KEY, $array);
        self::assertEquals(self::TEST_INT_VALUE, $array[self::TEST_INT_KEY]);
        
        self::assertArrayHasKey(self::TEST_OBJ_KEY, $array);
        self::assertInstanceOf(\stdClass::class, $array[self::TEST_OBJ_KEY]);
        self::assertEquals($this->testObjValue, $array[self::TEST_OBJ_KEY]);
    }

    public function testClassSerialize()
    {
        $serialObj = $this->testObj->serialize();

        self::assertIsString($serialObj);
        self::assertStringStartsWith('a:', $serialObj);
        self::assertStringContainsString(
            $this->formPropertyPair(self::TEST_STR_KEY, self::TEST_STR_VALUE),
            $serialObj
            );
        self::assertStringContainsString(
            $this->formPropertyPair(self::TEST_INT_KEY, self::TEST_INT_VALUE),
            $serialObj
            );
        self::assertStringContainsString(
            $this->formPropertyPair(self::TEST_OBJ_KEY, serialize($this->testObjValue)),
            $serialObj
            );
    }

    public function testSerialize()
    {
        $serialObj = serialize($this->testObj);

        self::assertIsString($serialObj);
        self::assertStringStartsWith(self::$serialObjectPrefix, $serialObj);
        self::assertStringContainsString(
            $this->formPropertyPair(self::TEST_STR_KEY, self::TEST_STR_VALUE),
            $serialObj
            );
        self::assertStringContainsString(
            $this->formPropertyPair(self::TEST_INT_KEY, self::TEST_INT_VALUE),
            $serialObj
            );
        self::assertStringContainsString(
            $this->formPropertyPair(self::TEST_OBJ_KEY, serialize($this->testObjValue)),
            $serialObj
            );
    }

    public function testMagicUnserialize()
    {
        $array = array (
            self::TEST_STR_KEY => self::TEST_ALT_STR_VALUE,
            self::TEST_INT_KEY => self::TEST_ALT_INT_VALUE,
            self::TEST_EXTRA_KEY => self::TEST_STR_VALUE
        );

        $this->testObj->__unserialize($array);

        self::assertEquals(self::TEST_ALT_STR_VALUE, $this->getTestObjProperty(self::TEST_STR_KEY));
        self::assertEquals(self::TEST_ALT_INT_VALUE, $this->getTestObjProperty(self::TEST_INT_KEY));
        self::assertEquals($this->testObjValue, $this->getTestObjProperty(self::TEST_OBJ_KEY));
        
        self::assertNull($this->getTestObjProperty(self::TEST_EXTRA_KEY));
    }

    public function testClassUnserialize()
    {
        $serialStr = sprintf(
            'a:2:{s:%d:"%s";s:%d:"%s";s:%d:"%s";i:%d;}',
            strlen(self::TEST_STR_KEY),
            self::TEST_STR_KEY,
            strlen(self::TEST_ALT_STR_VALUE),
            self::TEST_ALT_STR_VALUE,
            strlen(self::TEST_INT_KEY),
            self::TEST_INT_KEY,
            self::TEST_ALT_INT_VALUE
            );

        $this->testObj->unserialize($serialStr);

        self::assertEquals(self::TEST_ALT_STR_VALUE, $this->getTestObjProperty(self::TEST_STR_KEY));
        self::assertEquals(self::TEST_ALT_INT_VALUE, $this->getTestObjProperty(self::TEST_INT_KEY));

        self::assertEquals($this->testObjValue, $this->getTestObjProperty(self::TEST_OBJ_KEY));
    }

    public function testUnserialize()
    {
        $serialStr = sprintf(
            '%s%d:{s:%d:"%s";s:%d:"%s";s:%d:"%s";i:%d;s:%d:"%s";%s}',
            self::$serialObjectPrefix,
            3,
            strlen(self::TEST_STR_KEY),
            self::TEST_STR_KEY,
            strlen(self::TEST_ALT_STR_VALUE),
            self::TEST_ALT_STR_VALUE,
            strlen(self::TEST_INT_KEY),
            self::TEST_INT_KEY,
            self::TEST_ALT_INT_VALUE,
            strlen(self::TEST_OBJ_KEY),
            self::TEST_OBJ_KEY,
            serialize($this->testObjValue)
            );

        $this->testObj = unserialize($serialStr);

        self::assertEquals(self::TEST_ALT_STR_VALUE, $this->getTestObjProperty(self::TEST_STR_KEY));
        self::assertEquals(self::TEST_ALT_INT_VALUE, $this->getTestObjProperty(self::TEST_INT_KEY));

        self::assertEquals($this->testObjValue, $this->getTestObjProperty(self::TEST_OBJ_KEY));
    }
    
    public function testPreSerialize()
    {
        $testObject = new \stdClass();

        ReflectionHelper::setObjectProperty(
            TestSerializeAsArrayTrait::class,
            self::TEST_OBJ_KEY,
            $testObject,
            $this->testObj
            );

        $serialObj = serialize($this->testObj);
        
        self::assertIsString($serialObj);
        self::assertStringStartsWith(self::$serialObjectPrefix, $serialObj);
        self::assertStringNotContainsString(self::TEST_NEW_OBJ_KEY, $serialObj);
    }

    public function testPreUnserialize()
    {
        $obj = $this->getTestObjProperty(self::TEST_OBJ_KEY);
        $obj->classValue = -1;
        ReflectionHelper::setObjectProperty(get_class($this->testObj), self::TEST_OBJ_KEY, $obj, $this->testObj);

        $serialStr = serialize($this->testObj);
        
        $this->testObj = unserialize($serialStr);
        
        self::assertNotNull($this->testObj);
        
        $obj = $this->getTestObjProperty(self::TEST_OBJ_KEY);
        
        self::assertInstanceOf(\stdClass::class, $obj);
        self::assertEquals(1, $obj->classValue);
    }
    
    public function testPostUnserialize()
    {
        self::assertNull(
            ReflectionHelper::getObjectProperty(
                TestSerializeAsArrayTrait::class,
                self::TEST_NEW_OBJ_KEY,
                $this->testObj
                )
        );
        
        $serialStr = sprintf(
            '%s%d:{s:%d:"%s";s:%d:"%s";s:%d:"%s";i:%d;s:%d:"%s";%s}',
            self::$serialObjectPrefix,
            3,
            strlen(self::TEST_STR_KEY),
            self::TEST_STR_KEY,
            strlen(self::TEST_ALT_STR_VALUE),
            self::TEST_ALT_STR_VALUE,
            strlen(self::TEST_INT_KEY),
            self::TEST_INT_KEY,
            self::TEST_ALT_INT_VALUE,
            strlen(self::TEST_OBJ_KEY),
            self::TEST_OBJ_KEY,
            serialize($this->testObjValue)
            );
        
        $this->testObj = unserialize($serialStr);

        $objVal = ReflectionHelper::getObjectProperty(
            TestSerializeAsArrayTrait::class,
            self::TEST_OBJ_KEY,
            $this->testObj
            );
            
        self::assertNotNull($objVal);
        self::assertInstanceOf(\stdClass::class, $objVal);
    }
    
    private function getTestObjProperty(string $prop)
    {
        return ReflectionHelper::getObjectProperty(get_class($this->testObj), $prop, $this->testObj);
    }

    private function formPropertyPair(string $prop, mixed $value): string
    {
        if (is_string($value)) {
            $value = trim($value);
            if ('O' == $value[0] && ':' == $value[1]) {
                $valStr = $value;
            } else {
                $valStr = sprintf('s:%d:"%s"', strlen($value), $value);
            }
        } elseif (is_int($value)) {
            $valStr = sprintf('i:%d', $value);
        }

        return sprintf('s:%d:"%s";%s', strlen($prop), $prop, $valStr);
    }
}

class TestSerializeAsArrayTrait {
    use SerializeAsArrayTrait;

    protected string $strValue;
    protected int $intValue;
    protected object $objValue;
    protected ?object $newObjValue = null;

    public function __construct(string $strValue, int $intValue, object $objValue)
    {
        $this->strValue = $strValue;
        $this->intValue = $intValue;
        $this->objValue = $objValue;
    }
    
    protected function preSerialize(array &$vars): void
    {
        unset($vars[SerializeAsArrayTraitTest::TEST_NEW_OBJ_KEY]);
    }

    protected function preUnserialize(array &$vars): void
    {
        if (isset($vars[SerializeAsArrayTraitTest::TEST_OBJ_KEY]) &&
            $vars[SerializeAsArrayTraitTest::TEST_OBJ_KEY] instanceof \stdClass &&
            $vars[SerializeAsArrayTraitTest::TEST_OBJ_KEY]->classValue = -1
        ) {
            $vars[SerializeAsArrayTraitTest::TEST_OBJ_KEY]->classValue = 1;
        }
    }

    protected function postUnserialize(): void
    {
        $this->newObjValue = new \stdClass();
    }
}
