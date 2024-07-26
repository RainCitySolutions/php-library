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

        self::assertNotNull($map);
        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        self::assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        self::assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);

        self::assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_IDENTITY]);
        self::assertEquals(self::PROPERTY_FULLNAME, $map[self::COLUMN_FULLNAME]);
        self::assertEquals(self::PROPERTY_DATEOFBIRTH, $map[self::COLUMN_DATEOFBIRTH]);
    }

    public function testGetColumnNames()
    {
        $names = CsvBindByNameTraitTestClass::getColumnNames();

        self::assertNotNull($names);
        self::assertIsArray($names);
        self::assertNotEmpty($names);
        self::assertCount(3, $names);

        self::assertContains(self::COLUMN_IDENTITY, $names);
        self::assertContains(self::COLUMN_FULLNAME, $names);
        self::assertContains(self::COLUMN_DATEOFBIRTH, $names);
    }

    public function testGetFieldValues()
    {
        $map = CsvBindByNameTraitTestClass::getColumnValues($this->testObj);

        self::assertNotNull($map);
        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        self::assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        self::assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);

        self::assertEquals($this->testObj->id, $map[self::COLUMN_IDENTITY]);
        self::assertEquals($this->testObj->fullname, $map[self::COLUMN_FULLNAME]);
        self::assertEquals($this->testObj->dateOfBirth, $map[self::COLUMN_DATEOFBIRTH]);
    }


    public function testGetColumnPropertyMap_subClass()
    {
        $map = CsvBindByNameSubClass::getColumnPropertyMap();

        self::assertNotNull($map);
        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        self::assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        self::assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);
        self::assertArrayHasKey(self::COLUMN_SUBCLASSPROP, $map);

        self::assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_IDENTITY]);
        self::assertEquals(self::PROPERTY_FULLNAME, $map[self::COLUMN_FULLNAME]);
        self::assertEquals(self::PROPERTY_DATEOFBIRTH, $map[self::COLUMN_DATEOFBIRTH]);
        self::assertEquals(self::PROPERTY_SUBCLASSPROP, $map[self::COLUMN_SUBCLASSPROP]);
    }

    public function testGetColumnNames_subClass()
    {
        $names = CsvBindByNameSubClass::getColumnNames();

        self::assertNotNull($names);
        self::assertIsArray($names);
        self::assertNotEmpty($names);
        self::assertCount(4, $names);

        self::assertContains(self::COLUMN_IDENTITY, $names);
        self::assertContains(self::COLUMN_FULLNAME, $names);
        self::assertContains(self::COLUMN_DATEOFBIRTH, $names);
        self::assertContains(self::COLUMN_SUBCLASSPROP, $names);
    }

    public function testGetFieldValues_subClass()
    {
        $map = CsvBindByNameSubClass::getColumnValues($this->testSubclassObj);

        self::assertNotNull($map);
        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(self::COLUMN_IDENTITY, $map);
        self::assertArrayHasKey(self::COLUMN_FULLNAME, $map);
        self::assertArrayHasKey(self::COLUMN_DATEOFBIRTH, $map);
        self::assertArrayHasKey(self::COLUMN_SUBCLASSPROP, $map);

        self::assertEquals($this->testSubclassObj->id, $map[self::COLUMN_IDENTITY]);
        self::assertEquals($this->testSubclassObj->fullname, $map[self::COLUMN_FULLNAME]);
        self::assertEquals($this->testSubclassObj->dateOfBirth, $map[self::COLUMN_DATEOFBIRTH]);
        self::assertEquals($this->testSubclassObj->subClassProp, $map[self::COLUMN_SUBCLASSPROP]);
    }

    public function testGetColumnPropertyMap_withAlternate()
    {
        $map = CsvBindByNameAltsTestClass::getColumnPropertyMap();

        self::assertNotNull($map);
        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(self::COLUMN_NAMES_IDENTITY, $map);
        self::assertArrayHasKey(self::COLUMN_NAMES_MEMBERSHIP, $map);

        self::assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_NAMES_IDENTITY]);
        self::assertEquals(self::PROPERTY_IDENTITY, $map[self::COLUMN_NAMES_MEMBERSHIP]);
    }

    public function testGetColumnNames_withAlternate()
    {
        $names = CsvBindByNameAltsTestClass::getColumnNames();

        self::assertNotNull($names);
        self::assertIsArray($names);
        self::assertNotEmpty($names);
        self::assertCount(2, $names);

        self::assertContains(self::COLUMN_NAMES_IDENTITY, $names);
        self::assertContains(self::COLUMN_NAMES_MEMBERSHIP, $names);
    }

    public function testGetFieldValues_withAlternate()
    {
        $map = CsvBindByNameAltsTestClass::getColumnValues($this->testNamesObj);

        self::assertNotNull($map);
        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(self::COLUMN_NAMES_IDENTITY, $map);
        self::assertArrayHasKey(self::COLUMN_NAMES_MEMBERSHIP, $map);

        self::assertEquals($this->testNamesObj->id, $map[self::COLUMN_NAMES_IDENTITY]);
        self::assertEquals($this->testNamesObj->id, $map[self::COLUMN_NAMES_MEMBERSHIP]);
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
