<?php
namespace RainCity\Csv;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

/**
 * @covers \RainCity\Csv\CsvBindByName
 *
 */
class CsvBindByNameTest extends RainCityTestCase
{
    private const TEST_COLUMN1 = 'Test Column 1';
    private const TEST_COLUMN2 = 'Test Column 2';

    public function testCtor_nullParam()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvBindByName(null);    // NOSONAR
    }

    public function testCtor_emptyString()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvBindByName('');  // NOSONAR
    }

    public function testCtor_emptyArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvBindByName([]);  // NOSONAR
    }

    public function testCtor_blankArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvBindByName('   ');  // NOSONAR
    }

    public function testCtor_nonStringOrArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvBindByName(new \stdClass());  // NOSONAR
    }

    public function testCtor_nonStringArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CsvBindByName(['a', 2,]);  // NOSONAR
    }

    public function testCtor_string()
    {
        $testObj = new CsvBindByName(self::TEST_COLUMN1);

        $columns = ReflectionHelper::getObjectProperty(CsvBindByName::class, 'columns', $testObj);

        self::assertIsArray($columns);
        self::assertCount(1, $columns);
        self::assertEquals(self::TEST_COLUMN1, array_shift($columns));
    }

    public function testCtor_paddedString()
    {
        $testObj = new CsvBindByName('  '.self::TEST_COLUMN2.'    ');

        $columns = ReflectionHelper::getObjectProperty(CsvBindByName::class, 'columns', $testObj);

        self::assertIsArray($columns);
        self::assertCount(1, $columns);
        self::assertEquals(self::TEST_COLUMN2, array_shift($columns));
    }

    public function testCtor_array()
    {
        $testObj = new CsvBindByName([self::TEST_COLUMN1, self::TEST_COLUMN2]);

        $columns = ReflectionHelper::getObjectProperty(CsvBindByName::class, 'columns', $testObj);

        self::assertIsArray($columns);
        self::assertCount(2, $columns);
        self::assertEquals(self::TEST_COLUMN1, array_shift($columns));
        self::assertEquals(self::TEST_COLUMN2, array_shift($columns));
    }

    public function testGetColumns()
    {
        $testObj = new CsvBindByName(self::TEST_COLUMN1);

        ReflectionHelper::setObjectProperty(CsvBindByName::class, 'columns', array(self::TEST_COLUMN2), $testObj);

        $columns = $testObj->getColumns();

        self::assertNotNull($columns);
        self::assertIsArray($columns);
        self::assertCount(1, $columns);

        self::assertEquals(self::TEST_COLUMN2, array_shift($columns));
    }
}
