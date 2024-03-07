<?php declare(strict_types=1);
namespace RainCity\Json;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

class JsonEntityTest extends RainCityTestCase
{
    private const FIELD_ID     = 'idField';
    private const FIELD_NAME   = 'nameField';
    private const FIELD_NUMBER = 'numberField';

    private const PROPERTY_ID     = 'idProp';
    private const PROPERTY_NAME   = 'nameProp';
    private const PROPERTY_NUMBER = 'numberProp';

    public static $testPropertyMap;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$testPropertyMap = [
            new FieldPropertyEntry(self::FIELD_ID, self::PROPERTY_ID),
            new FieldPropertyEntry(self::FIELD_NUMBER, self::PROPERTY_NUMBER),
            new FieldPropertyEntry(self::FIELD_NAME, self::PROPERTY_NAME)
        ];
    }

    public function testGetJsonFields_noMap()
    {
        $obj = new EmptyMapEntityTestClass();
        
        $this->assertEmpty($obj->getJsonFields());
    }
    
    public function testGetJsonFields_justProps()
    {
        $obj = new JustClassPropsTestClass();

        $fields = $obj->getJsonFields();

        $this->assertNotEmpty($fields);
        $this->assertContains('intVal', $fields);
        $this->assertContains('strVal', $fields);
    }
    
    public function testGetJsonFields_withMap()
    {
        $obj = new ByNameEntityTestClass();

        $fields = $obj->getJsonFields();

        $this->assertNotEmpty($fields);
        $this->assertCount(count(self::$testPropertyMap), $fields);
        $this->assertContains(self::FIELD_ID, $fields);
        $this->assertContains(self::FIELD_NUMBER, $fields);
    }

    public function testGetJsonFields_propsAndMap()
    {
        $testObj = new class() extends JsonEntity
        {
            public int $intValue;
            public string $strValue;
            
            protected static function getFieldPropertyMap(): array
            {
                return [
                    new FieldPropertyEntry('jsonField', 'intValue')
                ];
            }
        };
        
        $fields = $testObj->getJsonFields();
        
        $this->assertNotEmpty($fields);
        $this->assertCount(2, $fields);
        $this->assertContains('jsonField', $fields);
        $this->assertContains('strValue', $fields);
    }

    public function testGetRenameMapping_noMap()
    {
        $obj = new EmptyMapEntityTestClass();

        $rename = $obj->getRenameMapping();

        $this->assertNotNull($rename);

        $mappings = ReflectionHelper::getObjectProperty(get_class($rename), 'mapping', $rename);

        $this->assertEmpty($mappings);
    }

    public function testGetRenameMapping_fieldMap()
    {
        $obj = new ByNameEntityTestClass();

        $rename = $obj->getRenameMapping();
        
        $this->assertNotNull($rename);
        
        $mappings = ReflectionHelper::getObjectProperty(get_class($rename), 'mapping', $rename);
        
        $this->assertNotEmpty($mappings);
        $this->assertCount(count(self::$testPropertyMap), $mappings);

        for ($ndx = 0; $ndx < count($mappings); $ndx++) {
            $mapping = $mappings[$ndx];
            $fieldPropEntry = self::$testPropertyMap[$ndx];
            
            $clazz = ReflectionHelper::getObjectProperty(get_class($mapping), 'class', $mapping);
            $from  = ReflectionHelper::getObjectProperty(get_class($mapping), 'from', $mapping);
            $to    = ReflectionHelper::getObjectProperty(get_class($mapping), 'to', $mapping);
            
            $this->assertEquals(get_class($obj), $clazz);
            $this->assertEquals($fieldPropEntry->getField(), $from);
            $this->assertEquals($fieldPropEntry->getProperty(), $to);
        }
    }
    
    public function testGetRenameMapping_fieldMapByIndex()
    {
        $obj = new ByIndexEntityTestClass();
        
        $rename = $obj->getRenameMapping();
        
        $this->assertNotNull($rename);
        
        $mappings = ReflectionHelper::getObjectProperty(get_class($rename), 'mapping', $rename);
        
        $this->assertNotEmpty($mappings);
        $this->assertCount(count(self::$testPropertyMap), $mappings);
        
        for ($ndx = 0; $ndx < count($mappings); $ndx++) {
            $mapping = $mappings[$ndx];
            $fieldPropEntry = self::$testPropertyMap[$ndx];
            
            $clazz = ReflectionHelper::getObjectProperty(get_class($mapping), 'class', $mapping);
            $from  = ReflectionHelper::getObjectProperty(get_class($mapping), 'from', $mapping);
            $to    = ReflectionHelper::getObjectProperty(get_class($mapping), 'to', $mapping);
            
            $this->assertEquals(get_class($obj), $clazz);
            $this->assertEquals(strval($ndx), $from);
            $this->assertEquals($fieldPropEntry->getProperty(), $to);
        }
    }
}

class EmptyMapEntityTestClass extends JsonEntity
{
}

class ByNameEntityTestClass extends JsonEntity
{
    /**
     * {@inheritDoc}
     * @see \RainCity\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        return JsonEntityTest::$testPropertyMap;
    }
}

class ByIndexEntityTestClass extends JsonEntity
{
    /**
     * {@inheritDoc}
     * @see \RainCity\Json\JsonEntity::getFieldPropertyMap()
     */
    protected static function getFieldPropertyMap(): array
    {
        return JsonEntityTest::$testPropertyMap;
    }

    /**
     * {@inheritDoc}
     * @see \RainCity\Json\JsonEntity::isMapByIndex()
     */
    protected static function isMapByIndex(): bool
    {
        return true;
    }
}

class JustClassPropsTestClass extends JsonEntity
{
    public int $intVal;
    public string $strVal;
}
