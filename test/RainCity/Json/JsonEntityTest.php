<?php declare(strict_types=1);
namespace RainCity\Json;

use JsonMapper\Middleware\Rename\Rename;
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

    /**
     * {@inheritDoc}
     * @see \RainCity\TestHelper\RainCityTestCase::tearDown()
     */
    protected function tearDown(): void
    {
        // Reset static fields in test JSON class
        JsonEntityTestClass::$fieldMap = array();
        JsonEntityTestClass::$byIndex = false;

        parent::tearDown();
    }

    public function testGetFields_noMap()
    {
        $obj = new JsonEntityTestClass();

        $this->assertEmpty($obj->getFields());
    }
    
    public function testGetFields_withMap()
    {
        $obj = new JsonEntityTestClass();

        JsonEntityTestClass::$fieldMap = array (
            new FieldPropertyEntry(self::FIELD_ID, self::PROPERTY_ID),
            new FieldPropertyEntry(self::FIELD_NUMBER, self::PROPERTY_NUMBER),
        );

        $fields = $obj->getFields();

        $this->assertNotEmpty($fields);
        $this->assertCount(2, $fields);
        $this->assertContains(self::FIELD_ID, $fields);
        $this->assertContains(self::FIELD_NUMBER, $fields);
    }

    public function testGetRenameMapping_noMap()
    {
        $obj = new JsonEntityTestClass();

        /** @var Rename */
        $rename = $obj->getRenameMapping();

        $this->assertNotNull($rename);

        $mappings = ReflectionHelper::getObjectProperty(get_class($rename), 'mapping', $rename);

        $this->assertEmpty($mappings);
    }

    public function testGetRenameMapping_fieldMap()
    {
        $obj = new JsonEntityTestClass();

        JsonEntityTestClass::$fieldMap = array (
            new FieldPropertyEntry(self::FIELD_NAME, self::PROPERTY_NAME),
            new FieldPropertyEntry(self::FIELD_NUMBER, self::PROPERTY_NUMBER),
        );
        
        /** @var Rename */
        $rename = $obj->getRenameMapping();
        
        $this->assertNotNull($rename);
        
        $mappings = ReflectionHelper::getObjectProperty(get_class($rename), 'mapping', $rename);
        
        $this->assertNotEmpty($mappings);
        $this->assertCount(count(JsonEntityTestClass::$fieldMap), $mappings);

        for ($ndx = 0; $ndx < count($mappings); $ndx++) {
            $mapping = $mappings[$ndx];
            $fieldPropEntry = JsonEntityTestClass::$fieldMap[$ndx];
            
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
        $obj = new JsonEntityTestClass();
        
        JsonEntityTestClass::$fieldMap = array (
            new FieldPropertyEntry(self::FIELD_ID, self::PROPERTY_ID),
            new FieldPropertyEntry(self::FIELD_NAME, self::PROPERTY_NAME),
            new FieldPropertyEntry(self::FIELD_NUMBER, self::PROPERTY_NUMBER),
        );
        JsonEntityTestClass::$byIndex = true;
        
        /** @var Rename */
        $rename = $obj->getRenameMapping();
        
        $this->assertNotNull($rename);
        
        $mappings = ReflectionHelper::getObjectProperty(get_class($rename), 'mapping', $rename);
        
        $this->assertNotEmpty($mappings);
        $this->assertCount(count(JsonEntityTestClass::$fieldMap), $mappings);
        
        for ($ndx = 0; $ndx < count($mappings); $ndx++) {
            $mapping = $mappings[$ndx];
            $fieldPropEntry = JsonEntityTestClass::$fieldMap[$ndx];
            
            $clazz = ReflectionHelper::getObjectProperty(get_class($mapping), 'class', $mapping);
            $from  = ReflectionHelper::getObjectProperty(get_class($mapping), 'from', $mapping);
            $to    = ReflectionHelper::getObjectProperty(get_class($mapping), 'to', $mapping);
            
            $this->assertEquals(get_class($obj), $clazz);
            $this->assertEquals(strval($ndx), $from);
            $this->assertEquals($fieldPropEntry->getProperty(), $to);
        }
    }
}
