<?php declare(strict_types=1);
namespace RainCity;

use PHPUnit\Framework\TestCase;
use RainCity\TestHelper\ReflectionHelper;

/**
 *
 * @covers \RainCity\SerializeAsArrayTrait
 *
 */
class SerializeAsArrayTraitTest extends TestCase
{
    private const TEST_STR_KEY = 'strValue';
    private const TEST_EXTRA_KEY = 'extraValue';
    private const TEST_INT_KEY = 'intValue';
    private const TEST_OBJECT_KEY = 'objValue';
    
    public const TEST_STR_VALUE = 'TestValue';
    public const TEST_ALT_STR_VALUE = 'AltTestValue';
    public const TEST_INT_VALUE = 85185105;
    public const TEST_ALT_INT_VALUE = 50158158;

    private static $serialObjectPrefix;
    private $testObj;


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
        $this->testObj = new TestSerializeAsArrayTrait(self::TEST_STR_VALUE, self::TEST_INT_VALUE);
    }

    public function testMagicSerialize()
    {
        $array = $this->testObj->__serialize();

        $this->assertArrayHasKey(self::TEST_STR_KEY, $array);
        $this->assertEquals(self::TEST_STR_VALUE, $array[self::TEST_STR_KEY]);

        $this->assertArrayHasKey(self::TEST_INT_KEY, $array);
        $this->assertEquals(self::TEST_INT_VALUE, $array[self::TEST_INT_KEY]);
    }

    public function testClassSerialize()
    {
        $serialObj = $this->testObj->serialize();

        $this->assertIsString($serialObj);
        $this->assertStringStartsWith('a:', $serialObj);
        $this->assertStringContainsString(
            $this->formPropertyPair(self::TEST_STR_KEY, self::TEST_STR_VALUE),
            $serialObj
            );
        $this->assertStringContainsString(
            $this->formPropertyPair(self::TEST_INT_KEY, self::TEST_INT_VALUE),
            $serialObj
            );
    }

    public function testSerialize()
    {
        $serialObj = serialize($this->testObj);

        $this->assertIsString($serialObj);
        $this->assertStringStartsWith(self::$serialObjectPrefix, $serialObj);
        $this->assertStringContainsString(
            $this->formPropertyPair(self::TEST_STR_KEY, self::TEST_STR_VALUE),
            $serialObj
            );
        $this->assertStringContainsString(
            $this->formPropertyPair(self::TEST_INT_KEY, self::TEST_INT_VALUE),
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

        $this->assertEquals(self::TEST_ALT_STR_VALUE, $this->getTestObjProperty(self::TEST_STR_KEY));
        $this->assertEquals(self::TEST_ALT_INT_VALUE, $this->getTestObjProperty(self::TEST_INT_KEY));
        $this->assertEquals(null, $this->getTestObjProperty(self::TEST_EXTRA_KEY));
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

        $this->assertEquals(self::TEST_ALT_STR_VALUE, $this->getTestObjProperty(self::TEST_STR_KEY));
        $this->assertEquals(self::TEST_ALT_INT_VALUE, $this->getTestObjProperty(self::TEST_INT_KEY));
    }

    public function testUnserialize()
    {
        $serialStr = sprintf(
            '%s2:{s:%d:"%s";s:%d:"%s";s:%d:"%s";i:%d;}',
            self::$serialObjectPrefix,
            strlen(self::TEST_STR_KEY),
            self::TEST_STR_KEY,
            strlen(self::TEST_ALT_STR_VALUE),
            self::TEST_ALT_STR_VALUE,
            strlen(self::TEST_INT_KEY),
            self::TEST_INT_KEY,
            self::TEST_ALT_INT_VALUE
            );

        $this->testObj = unserialize($serialStr);

        $this->assertEquals(self::TEST_ALT_STR_VALUE, $this->getTestObjProperty(self::TEST_STR_KEY));
        $this->assertEquals(self::TEST_ALT_INT_VALUE, $this->getTestObjProperty(self::TEST_INT_KEY));
    }
    
    public function testPreSerialize()
    {
        $testObject = new \stdClass();

        ReflectionHelper::setObjectProperty(
            TestSerializeAsArrayTrait::class,
            self::TEST_OBJECT_KEY,
            $testObject,
            $this->testObj
            );

        $serialObj = serialize($this->testObj);
        
        $this->assertIsString($serialObj);
        $this->assertStringStartsWith(self::$serialObjectPrefix, $serialObj);
        $this->assertStringNotContainsString(self::TEST_OBJECT_KEY, $serialObj);
    }

    public function testPostUnserialize()
    {
        $this->assertNull(
            ReflectionHelper::getObjectProperty(
                TestSerializeAsArrayTrait::class,
                self::TEST_OBJECT_KEY,
                $this->testObj
                )
        );
        
        $serialStr = sprintf(
            '%s2:{s:%d:"%s";s:%d:"%s";s:%d:"%s";i:%d;}',
            self::$serialObjectPrefix,
            strlen(self::TEST_STR_KEY),
            self::TEST_STR_KEY,
            strlen(self::TEST_ALT_STR_VALUE),
            self::TEST_ALT_STR_VALUE,
            strlen(self::TEST_INT_KEY),
            self::TEST_INT_KEY,
            self::TEST_ALT_INT_VALUE
            );
        
        $this->testObj = unserialize($serialStr);

        $objVal = ReflectionHelper::getObjectProperty(
            TestSerializeAsArrayTrait::class,
            self::TEST_OBJECT_KEY,
            $this->testObj
            );
            
        $this->assertNotNull($objVal);
        $this->assertInstanceOf(\stdClass::class, $objVal);
    }
    
    private function getTestObjProperty(string $prop)
    {
        $result = null;

        try {
            $reflection = new \ReflectionClass($this->testObj);
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);

            $result = $property->getValue($this->testObj);
        } catch (\ReflectionException $re) {
            // empty catch
        }

        return $result;
    }

    private function formPropertyPair(string $prop, mixed $value): string
    {
        if (is_string($value)) {
            $valStr = sprintf('s:%d:"%s"', strlen($value), $value);
        } elseif (is_int($value)) {
            $valStr = sprintf('i:%d', $value);
        }

        return sprintf('s:%d:"%s";%s;', strlen($prop), $prop, $valStr);
    }
}

class TestSerializeAsArrayTrait {
    use SerializeAsArrayTrait;

    protected string $strValue;
    protected int $intValue;
    protected ?object $objValue = null;

    public function __construct(string $strValue, int $intValue)
    {
        $this->strValue = $strValue;
        $this->intValue = $intValue;
    }
    
    protected function preSerialize(array $vars): array
    {
        unset($vars['objValue']);

        return $vars;
    }
    
    protected function postUnserialize(): void
    {
        $this->objValue = new \stdClass();
    }
}
