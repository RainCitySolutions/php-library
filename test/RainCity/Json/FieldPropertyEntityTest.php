<?php  declare(strict_types=1);
namespace RainCity\Json;

use RainCity\TestHelper\ReflectionHelper;
use RainCity\TestHelper\RainCityTestCase;

class FieldPropertyEntityTest extends RainCityTestCase
{
    private const TEST_FIELD = 'testField';
    private const TEST_PROPERTY = 'testProperty';
    
    public function testCtor()
    {
        $testObj = new FieldPropertyEntry(self::TEST_FIELD, self::TEST_PROPERTY);

        $this->assertEquals(
            self::TEST_FIELD,
            ReflectionHelper::getObjectProperty(FieldPropertyEntry::class, 'field', $testObj)
            );

        $this->assertEquals(
            self::TEST_PROPERTY,
            ReflectionHelper::getObjectProperty(FieldPropertyEntry::class, 'property', $testObj)
            );
    }

    public function testGetField()
    {
        $testObj = new FieldPropertyEntry(self::TEST_FIELD, '');
        
        $this->assertEquals(
            self::TEST_FIELD,
            $testObj->getField()
            );
    }
    
    public function testGetProperty()
    {
        $testObj = new FieldPropertyEntry('', self::TEST_PROPERTY);
        
        $this->assertEquals(
            self::TEST_PROPERTY,
            $testObj->getProperty()
            );
    }
}
