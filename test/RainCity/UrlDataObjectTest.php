<?php
namespace RainCity;

/**
 * UrlDataObject test case.
 */
use RainCity\TestHelper\RainCityTestCase;

class UrlDataObjectTest extends RainCityTestCase
{
    private const TEST_STRING = 'test data';
    private const TEST_INTEGER = 3254;

    public function testToString_emptyArray()
    {
        $result = UrlDataObject::toString([]);

        $this->assertNull($result);
    }

    public function testToString_singleValue()
    {
        $testData = [self::TEST_STRING];

        $result = UrlDataObject::toString($testData);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function testToString_mixedValues()
    {
        $testData = [self::TEST_STRING, self::TEST_INTEGER];

        $result = UrlDataObject::toString($testData);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function testToString_deflateError()
    {
        $testData = [self::TEST_STRING];

        \Brain\Monkey\Functions\when('gzdeflate')->alias(fn () => false);

        $result = UrlDataObject::toString($testData);

        $this->assertNull($result);
    }


    public function testFromString_emptyString()
    {
        $result = UrlDataObject::fromString('');

        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFromString_noneBase64String()
    {
        $result = UrlDataObject::fromString('ABC!@#DEF8978');

        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFromString_success()
    {
        $testData = [self::TEST_INTEGER, self::TEST_STRING];

        $urlStr = UrlDataObject::toString($testData);

        $this->assertNotNull($urlStr);
        $this->assertNotEmpty($urlStr);

        $result = UrlDataObject::fromString($urlStr);

        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertCount(count($testData), $result);

        $this->assertEquals(array_shift($testData), intval(array_shift($result)));
        $this->assertEquals(array_shift($testData), array_shift($result));
    }

    public function testFromString_base64DecodeFailure()
    {
        $testData = [self::TEST_INTEGER, self::TEST_STRING];

        \Brain\Monkey\Functions\when('base64_decode')->alias(fn () => true);

        $urlStr = UrlDataObject::toString($testData);

        $this->assertNotNull($urlStr);
        $this->assertNotEmpty($urlStr);

        $result = UrlDataObject::fromString($urlStr);

        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFromString_gzinflateFailure()
    {
        $testData = [self::TEST_INTEGER, self::TEST_STRING];

        \Brain\Monkey\Functions\when('gzinflate')->alias(fn () => true);

        $urlStr = UrlDataObject::toString($testData);

        $this->assertNotNull($urlStr);
        $this->assertNotEmpty($urlStr);

        $result = UrlDataObject::fromString($urlStr);

        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
