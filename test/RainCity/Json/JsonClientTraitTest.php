<?php declare(strict_types=1);
namespace RainCity\Json;

use JsonMapper\JsonMapper;
use JsonMapper\Handler\FactoryRegistry;
use JsonMapper\Handler\PropertyMapper;
use Psr\SimpleCache\CacheInterface;
use RainCity\DataCache;
use RainCity\Json\Test\JsonClientTraitTestClass;
use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

class JsonClientTraitTest extends RainCityTestCase
{
    public const TEST_CACHE_TTL = 520;

    private JsonClientTraitTestClass $testObj;

    /**
     * {@inheritDoc}
     * @see \RainCity\TestHelper\RainCityTestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new JsonClientTraitTestClass();
    }
    
    public function testCtor_defaults()
    {
        $this->assertEquals(10, $this->getCacheDefaultTTL($this->testObj));
        $this->assertNotNull($this->getClassFactoryRegistry($this->testObj));
    }

    public function testCtor_setCacheTTL()
    {
        $localTestObj = new JsonClientTraitTestClass(JsonClientTraitTest::TEST_CACHE_TTL);
        
        $this->assertEquals(self::TEST_CACHE_TTL, $this->getCacheDefaultTTL($localTestObj));
    }

    public function testCtor_setRegistryFactory()
    {
        $testFactory = new FactoryRegistry();

        $localTestObj = new JsonClientTraitTestClass(
            JsonClientTraitTest::TEST_CACHE_TTL,
            $testFactory
            );
        
        $this->assertSame(
            $testFactory,
            $this->getClassFactoryRegistry($localTestObj)
            );
    }
    
    public function testGetCacheKey()
    {
        $key = ReflectionHelper::invokeObjectMethod(
            get_class($this->testObj),
            $this->testObj,
            'getCacheKey',
            __FUNCTION__
            );
        
        $this->assertNotNull($key);
        $this->assertStringContainsString('JsonClientTraitTestClass', $key);
        $this->assertStringContainsString(__FUNCTION__, $key);
        $this->assertStringNotContainsString('\\', $key);
    }
    
    public function testProcessJsonResponse_notJson()
    {
        $result = ReflectionHelper::invokeObjectMethod(
            get_class($this->testObj),
            $this->testObj,
            'processJsonResponse',
            'Hello World!',
            new JsonEntityTestClass()
            );
        
        $this->assertNull($result);
    }

    public function testProcessJsonResponse_singleObject()
    {
        list ($testJsonObj, $testJsonStr) = $this->generateJsonEntityObject();
        
        $result = ReflectionHelper::invokeObjectMethod(
            get_class($this->testObj),
            $this->testObj,
            'processJsonResponse',
            $testJsonStr,
            new JsonEntityTestClass()
            );
        
        $this->assertNotNull($result);
        $this->assertEquals($testJsonObj, $result);
    }

    public function testProcessJsonResponse_array()
    {
        $jsonObjArray = array();
        $jsonStrArray = array();

        $cnt = rand(1, 5);
        while ($cnt >= 1) {
            list ($testJsonObj, $testJsonStr) = $this->generateJsonEntityObject();

            array_push($jsonObjArray, $testJsonObj);
            array_push($jsonStrArray, $testJsonStr);

            $cnt--;
        }
        
        $result = ReflectionHelper::invokeObjectMethod(
            get_class($this->testObj),
            $this->testObj,
            'processJsonResponse',
            '['.join(', ', $jsonStrArray).']',
            new JsonEntityTestClass()
            );
        
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        
        foreach ($result as $ndx => $entry) {
            $this->assertEquals($jsonObjArray[$ndx], $entry);
        }
    }

    public function testProcessJsonResponse_listArray()
    {
        $jsonObjArray = array();
        $jsonStrArray = array();
        
        $cnt = rand(1, 5);
        while ($cnt >= 1) {
            list ($testJsonObj, $testJsonStr) = $this->generateJsonEntityList();
            
            array_push($jsonObjArray, $testJsonObj);
            array_push($jsonStrArray, $testJsonStr);
            
            $cnt--;
        }
        
        $result = ReflectionHelper::invokeObjectMethod(
            get_class($this->testObj),
            $this->testObj,
            'processJsonResponse',
            '['.join(', ', $jsonStrArray).']',
            new JsonEntityTestClass()
            );
        
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        
        foreach ($result as $ndx => $entry) {
            $this->assertEquals($jsonObjArray[$ndx], $entry);
        }
    }
    
    private function getCacheDefaultTTL(JsonClientTraitTestClass $testObj): int
    {
        /** @var CacheInterface */
        $cache = ReflectionHelper::getObjectProperty(get_class($testObj), 'cache', $testObj);

        return ReflectionHelper::getObjectProperty(DataCache::class, 'defaultTTL', $cache);
    }

    private function getClassFactoryRegistry(JsonClientTraitTestClass $testObj): ?FactoryRegistry
    {
        $classFactoryRegistry = null;

        /** @var JsonMapper */
        $jsonMapper = ReflectionHelper::getObjectProperty(
            get_class($testObj),
            'mapper',
            $testObj
            );
        if (isset($jsonMapper)) {
            /** @var PropertyMapper */
            $propertyMapper = ReflectionHelper::getObjectProperty(
                JsonMapper::class,
                'propertyMapper',
                $jsonMapper
                );
            if (isset($propertyMapper)) {
                /** @var FactoryRegistry */
                $classFactoryRegistry = ReflectionHelper::getObjectProperty(
                    PropertyMapper::class,
                    'classFactoryRegistry',
                    $propertyMapper
                    );
            }
        }

        return $classFactoryRegistry;
    }
    
    private function generateJsonEntityObject(): array
    {
        $obj = new JsonEntityTestClass();
        $obj->id = rand(1, 100);
        $obj->name = 'test-'.$obj->id;
        $obj->number = $obj->id * rand (1, 9);

        return array ($obj, json_encode($obj));
    }
    
    private function generateJsonEntityList(): array
    {
        $obj = new JsonEntityTestClass();
        $obj->id = rand(1, 100);
        $obj->name = 'test-'.$obj->id;
        $obj->number = $obj->id * rand(2, 9);
        
        $list = array($obj->id, $obj->name, $obj->number);
        
        return array ($obj, json_encode($list));
    }
}

class JsonEntityTestClass extends JsonEntity
{
    public int $id;
    public string $name;
    public int $number;
    
    protected static function isMapByIndex(): bool
    {
        return true;
    }

    protected static function getFieldPropertyMap(): array
    {
        return [
            new FieldPropertyEntry('doesnotmatter1', 'id'),
            new FieldPropertyEntry('doesnotmatter2', 'name'),
            new FieldPropertyEntry('doesnotmatter3', 'number')
            ];
    }
}
