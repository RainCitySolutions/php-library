<?php declare(strict_types=1);
namespace RainCity\Csv;

use RainCity\TestHelper\RainCityTestCase;


/**
 * @covers \RainCity\Csv\CsvBindByNameTrait
 *
 * @covers \RainCity\Csv\CsvBindByName::__construct
 * @covers \RainCity\Csv\CsvBindByName::getColumns
 */
class CsvBindByNameTraitTest extends RainCityTestCase
{
    private const COLUMN_IDENTITY = 'Identity';
    private const COLUMN_FULLNAME = 'Full Name';
    private const COLUMN_DATEOFBIRTH = 'DOB';
    private const COLUMN_SUBCLASSPROP = 'Sub Class Prop';
    private const COLUMN_NAMES_MEMBERSHIP = 'Membership ID';
    private const COLUMN_NAMES_IDENTITY = 'Identity ID';

    private const PROPERTY_IDENTITY = 'id';
    private const PROPERTY_FULLNAME = 'fullname';
    private const PROPERTY_DATEOFBIRTH = 'dateOfBirth';
    private const PROPERTY_SUBCLASSPROP = 'subClassProp';

    private const TEST_IDENTIFIER = 815151;
    private const TEST_FULLNAME = 'Mark Twain';
    private const TEST_SUBCLASSPROP = 'Charles Dickens';

    private CsvBindByNameTraitTestClass $testObj;
    private CsvBindByNameSubClass $testSubclassObj;
    private CsvBindByNameAltsTestClass $testNamesObj;

    /**
     * {@inheritDoc}
     * @see \RainCity\TestHelper\RainCityTestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new CsvBindByNameTraitTestClass();
        $this->testObj->id = self::TEST_IDENTIFIER;
        $this->testObj->fullname = self::TEST_FULLNAME;
        $this->testObj->dateOfBirth = new \DateTime();

        $this->testSubclassObj = new CsvBindByNameSubClass();
        $this->testSubclassObj->id = self::TEST_IDENTIFIER;
        $this->testSubclassObj->fullname = self::TEST_FULLNAME;
        $this->testSubclassObj->dateOfBirth = new \DateTime();
        $this->testSubclassObj->subClassProp = self::TEST_SUBCLASSPROP;

        $this->testNamesObj = new CsvBindByNameAltsTestClass();
        $this->testNamesObj->id = self::TEST_IDENTIFIER;
    }

    public function testGetColumnPropertyMap()
    {
        $map = CsvBindByNameTraitTestClass::getColumnPropertyMap();

        $this->assertNotNull($map);
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $this->assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        $this->assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        $this->assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);

        $this->assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_IDENTITY]);
        $this->assertEquals(self::PROPERTY_FULLNAME, $map[self::COLUMN_FULLNAME]);
        $this->assertEquals(self::PROPERTY_DATEOFBIRTH, $map[self::COLUMN_DATEOFBIRTH]);
    }

    public function testGetColumnNames()
    {
        $names = CsvBindByNameTraitTestClass::getColumnNames();

        $this->assertNotNull($names);
        $this->assertIsArray($names);
        $this->assertNotEmpty($names);
        $this->assertCount(3, $names);

        $this->assertContains(self::COLUMN_IDENTITY, $names);
        $this->assertContains(self::COLUMN_FULLNAME, $names);
        $this->assertContains(self::COLUMN_DATEOFBIRTH, $names);
    }

    public function testGetFieldValues()
    {
        $map = CsvBindByNameTraitTestClass::getColumnValues($this->testObj);

        $this->assertNotNull($map);
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $this->assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        $this->assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        $this->assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);

        $this->assertEquals($this->testObj->id, $map[self::COLUMN_IDENTITY]);
        $this->assertEquals($this->testObj->fullname, $map[self::COLUMN_FULLNAME]);
        $this->assertEquals($this->testObj->dateOfBirth, $map[self::COLUMN_DATEOFBIRTH]);
    }


    public function testGetColumnPropertyMap_subClass()
    {
        $map = CsvBindByNameSubClass::getColumnPropertyMap();

        $this->assertNotNull($map);
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $this->assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        $this->assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        $this->assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);
        $this->assertArrayHasKey(self::COLUMN_SUBCLASSPROP, $map);

        $this->assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_IDENTITY]);
        $this->assertEquals(self::PROPERTY_FULLNAME, $map[self::COLUMN_FULLNAME]);
        $this->assertEquals(self::PROPERTY_DATEOFBIRTH, $map[self::COLUMN_DATEOFBIRTH]);
        $this->assertEquals(self::PROPERTY_SUBCLASSPROP, $map[self::COLUMN_SUBCLASSPROP]);
    }

    public function testGetColumnNames_subClass()
    {
        $names = CsvBindByNameSubClass::getColumnNames();

        $this->assertNotNull($names);
        $this->assertIsArray($names);
        $this->assertNotEmpty($names);
        $this->assertCount(4, $names);

        $this->assertContains(self::COLUMN_IDENTITY, $names);
        $this->assertContains(self::COLUMN_FULLNAME, $names);
        $this->assertContains(self::COLUMN_DATEOFBIRTH, $names);
        $this->assertContains(self::COLUMN_SUBCLASSPROP, $names);
    }

    public function testGetFieldValues_subClass()
    {
        $map = CsvBindByNameSubClass::getColumnValues($this->testSubclassObj);

        $this->assertNotNull($map);
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $this->assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        $this->assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        $this->assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);
        $this->assertArrayHasKey(self::COLUMN_SUBCLASSPROP, $map);

        $this->assertEquals($this->testSubclassObj->id, $map[self::COLUMN_IDENTITY]);
        $this->assertEquals($this->testSubclassObj->fullname, $map[self::COLUMN_FULLNAME]);
        $this->assertEquals($this->testSubclassObj->dateOfBirth, $map[self::COLUMN_DATEOFBIRTH]);
        $this->assertEquals($this->testSubclassObj->subClassProp, $map[self::COLUMN_SUBCLASSPROP]);
    }

    public function testGetColumnPropertyMap_withAlternate()
    {
        $map = CsvBindByNameAltsTestClass::getColumnPropertyMap();

        $this->assertNotNull($map);
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $this->assertArrayHasKey(self::COLUMN_NAMES_IDENTITY, $map);
        $this->assertArrayHasKey(self::COLUMN_NAMES_MEMBERSHIP, $map);

        $this->assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_NAMES_IDENTITY]);
        $this->assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_NAMES_MEMBERSHIP]);
    }

    public function testGetColumnNames_withAlternate()
    {
        $names = CsvBindByNameAltsTestClass::getColumnNames();

        $this->assertNotNull($names);
        $this->assertIsArray($names);
        $this->assertNotEmpty($names);
        $this->assertCount(2, $names);

        $this->assertContains(self::COLUMN_NAMES_IDENTITY, $names);
        $this->assertContains(self::COLUMN_NAMES_MEMBERSHIP, $names);
    }

    public function testGetFieldValues_withAlternate()
    {
        $map = CsvBindByNameAltsTestClass::getColumnValues($this->testNamesObj);

        $this->assertNotNull($map);
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $this->assertArrayHasKey(self::COLUMN_NAMES_IDENTITY, $map);
        $this->assertArrayHasKey(self::COLUMN_NAMES_MEMBERSHIP, $map);

        $this->assertEquals($this->testNamesObj->id, $map[self::COLUMN_NAMES_IDENTITY]);
        $this->assertEquals($this->testNamesObj->id, $map[self::COLUMN_NAMES_MEMBERSHIP]);
    }
}

class CsvBindByNameTraitTestClass
{
    use CsvBindByNameTrait;

    /** @CsvBindByName(column = "Identity") */
    public int $id;

    /** @CsvBindByName(column = "Full Name") */
    public string $fullname;

    /** @CsvBindByName(column = "DOB") */
    public \DateTime $dateOfBirth;
}

class CsvBindByNameSubClass extends CsvBindByNameTraitTestClass
{
    /** @CsvBindByName(column = "Sub Class Prop") */
    public string $subClassProp;
}

class CsvBindByNameAltsTestClass
{
    use CsvBindByNameTrait;

    /**
     * @CsvBindByName(column = {"Membership ID", "Identity ID"})
     */
    public int $id;
}
