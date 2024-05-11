<?php declare(strict_types=1);
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use RainCity\TestHelper\ReflectionHelper;

/**
 * DataCache test case.
 *
 * @covers \RainCity\DataCache
 * @covers \RainCity\Logging\Logger::getLogger
 *
 */
class DataCacheTest extends RainCityTestCase
{
    /** @var ArrayAdapter */
    private ArrayAdapter $cacheAdapter;

    private DataCache $dataCache;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheAdapter = new ArrayAdapter();
        $this->dataCache = new DataCache($this->cacheAdapter);
    }

    public function testInstance()
    {
        $tmpAdapter = new ArrayAdapter();
        $tmpTTL = 5;

        $instCache = DataCache::instance($tmpAdapter, 5);

        $this->assertNotSame($this->dataCache, $instCache);
        $this->assertEquals(
            $tmpAdapter,
            ReflectionHelper::getObjectProperty(get_class($instCache), 'cache', $instCache)
            );
        $this->assertEquals(
            $tmpTTL,
            ReflectionHelper::getObjectProperty(get_class($instCache), 'defaultTTL', $instCache)
            );
    }

    public function testSet()
    {
        $testKey = 'testSetKey';
        $testValue = 'testSetValue';

        $result = $this->dataCache->set($testKey, $testValue);

        $this->assertTrue($result);

        $cachedValue = $this->getFromAdapter($testKey);

        $this->assertNotNull($cachedValue);
        $this->assertEquals($testValue, $cachedValue);
    }

    public function testGet_noValue()
    {
        $testKey = 'testGetKey';

        $cachedValue = $this->dataCache->get($testKey);

        $this->assertNull($cachedValue);
    }

    public function testGet_default()
    {
        $testKey = 'testGetKey';
        $testValue = 'testGetValue';

        $cachedValue = $this->dataCache->get($testKey, $testValue);

        $this->assertNotNull($cachedValue);
        $this->assertEquals($testValue, $cachedValue);

        $this->assertEquals($testValue, $this->getFromAdapter($testKey));
    }

    public function testHas_present()
    {
        $testKey = 'testHasKey';
        $testValue = 'testGetValue';

        $this->assertFalse($this->cacheAdapter->hasItem($testKey));

        $this->addToAdapter($testKey, $testValue);

        $hasValue = $this->dataCache->has($testKey);

        $this->assertTrue($hasValue);
    }

    public function testHas_missing()
    {
        $testKey = 'testHasMissingKey';

        $this->assertFalse($this->cacheAdapter->hasItem($testKey));

        $hasValue = $this->dataCache->has($testKey);

        $this->assertFalse($hasValue);
    }

    public function testDelete_present()
    {
        $testKey = 'testDeleteKey';
        $testValue = 'testDeleteValue';

        $this->addToAdapter($testKey, $testValue);

        $result = $this->dataCache->delete($testKey);

        $this->assertTrue($result);

        $this->assertFalse($this->cacheAdapter->hasItem($testKey));
    }

    public function testDelete_missing()
    {
        $testKey = 'testDeleteMissingKey';

        $result = $this->dataCache->delete($testKey);

        $this->assertTrue($result);

        $this->assertFalse($this->cacheAdapter->hasItem($testKey));
    }

    public function testClear()
    {
        $testKey1 = 'testClearKey1';
        $testValue1 = 'testClearValue1';
        $testKey2 = 'testClearKey2';
        $testValue2 = 'testClearValue2';

        $this->addToAdapter($testKey1, $testValue1);
        $this->addToAdapter($testKey2, $testValue2);

        $this->assertTrue($this->dataCache->has($testKey1));
        $this->assertTrue($this->dataCache->has($testKey2));

        $result = $this->dataCache->clear();

        $this->assertTrue($result);

        $this->assertCount(0, $this->cacheAdapter->getValues());
    }

    public function testSetMultiple()
    {
        $testKey1 = 'testSetMultipleKey1';
        $testValue1 = 'testSetMultipleValue1';
        $testKey2 = 'testSetMultipleKey2';
        $testValue2 = 'testSetMultipleValue2';

        $result = $this->dataCache->setMultiple(array(
            $testKey1 => $testValue1,
            $testKey2 => $testValue2
        ));

        $this->assertTrue($result);

        $this->assertEquals($testValue1, $this->getFromAdapter($testKey1));
        $this->assertEquals($testValue2, $this->getFromAdapter($testKey2));
    }

    public function testGetMultiple()
    {
        $testKey1 = 'testGetMultipleKey1';
        $testValue1 = 'testGetMultipleValue1';
        $testKey2 = 'testGetMultipleKey2';
        $testValue2 = 'testGetMultipleValue2';

        $this->addToAdapter($testKey1, $testValue1);
        $this->addToAdapter($testKey2, $testValue2);

        $result = $this->dataCache->getMultiple(array($testKey1, $testKey2));

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals($testValue1, $result[$testKey1]);
        $this->assertEquals($testValue2, $result[$testKey2]);
    }

    public function testGetMultiple_default()
    {
        $testKey1 = 'testGetMultipleKey1';
        $testKey2 = 'testGetMultipleKey2';

        $defaultValue = 'testGetMultipleDefault';

        $result = $this->dataCache->getMultiple(array($testKey1, $testKey2), $defaultValue);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals($defaultValue, $result[$testKey1]);
        $this->assertEquals($defaultValue, $result[$testKey2]);

        $this->assertEquals($defaultValue, $this->getFromAdapter($testKey1));
        $this->assertEquals($defaultValue, $this->getFromAdapter($testKey2));
    }

    public function testGetMultiple_someDefault()
    {
        $testKey1 = 'testGetMultipleKey1';
        $testKey2 = 'testGetMultipleKey2';
        $testKey3 = 'testGetMultipleKey3';

        $defaultValue = 'testGetMultipleDefault';
        $testValue2 = 'testGetMultipleValue2';

        $this->addToAdapter($testKey2, $testValue2);

        $result = $this->dataCache->getMultiple(
            array($testKey1, $testKey2, $testKey3),
            $defaultValue
            );

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        $this->assertEquals($defaultValue, $result[$testKey1]);
        $this->assertEquals($defaultValue, $result[$testKey3]);

        $this->assertEquals($testValue2, $result[$testKey2]);

        $this->assertEquals($defaultValue, $this->getFromAdapter($testKey1));
        $this->assertEquals($defaultValue, $this->getFromAdapter($testKey3));

        $this->assertEquals($testValue2, $this->getFromAdapter($testKey2));
    }

    public function testDeleteMultiple()
    {
        $testKey1 = 'testDeleteMultipleKey1';
        $testKey2 = 'testDeleteMultipleKey2';
        $testKey3 = 'testDeleteMultipleKey3';

        $testValue1 = 'testDeleteMultipleValue1';
        $testValue2 = 'testDeleteMultipleValue2';
        $testValue3 = 'testDeleteMultipleValue3';

        $this->addToAdapter($testKey1, $testValue1);
        $this->addToAdapter($testKey2, $testValue2);
        $this->addToAdapter($testKey3, $testValue3);

        $result = $this->dataCache->deleteMultiple(
            array($testKey2, $testKey3)
            );

        $this->assertTrue($result);

        $this->assertEquals($testValue1, $this->getFromAdapter($testKey1));

        $this->assertNull($this->getFromAdapter($testKey2));
        $this->assertNull($this->getFromAdapter($testKey3));
    }

    private function addToAdapter(string $key, mixed $value)
    {
        $cacheItem = $this->cacheAdapter->getItem($key);
        $cacheItem->set($value);
        $this->cacheAdapter->save($cacheItem);
    }

    private function getFromAdapter(string $key): mixed
    {
        return $this->cacheAdapter->getItem($key)->get();
    }
}

