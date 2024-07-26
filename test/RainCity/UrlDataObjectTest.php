<?php
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

/**
 * UrlDataObject test case.
 *
 * @covers \RainCity\UrlDataObject
 */
class UrlDataObjectTest extends RainCityTestCase // NOSONAR - too many methods
{
    private const TEST_KEY_A = 'testKeyA';
    private const TEST_KEY_B = 'testKeyB';
    private const TEST_KEY_C = 'testKeyC';

    private const TEST_STRING = 'test data';
    private const TEST_INTEGER = 3254;

    private const NON_BASE64_STR = 'ABC!@#DEF8978';

    private function buildEncodedString(): string
    {
        $setupObj = new UrlDataObject();
        $setupProp = new \stdClass();

        $setupProp->{self::TEST_KEY_A} = self::TEST_STRING;
        $setupProp->{self::TEST_KEY_B} = self::TEST_INTEGER;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $setupProp, $setupObj);

        return $setupObj->encode();
    }

    public function testSet_singleProp()
    {
        $testObj = new UrlDataObject();

        $testObj->set(self::TEST_KEY_A, self::TEST_STRING);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_A, $dataProp);

        $this->assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_A});
    }

    public function testSet_multiProp()
    {
        $testObj = new UrlDataObject();

        $testObj->set(self::TEST_KEY_A, self::TEST_INTEGER);
        $testObj->set(self::TEST_KEY_B, self::TEST_STRING);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        $this->assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_A});
        $this->assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_B});
    }

    public function testGet_singleProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testValue = $testObj->get(self::TEST_KEY_A);

        $this->assertNotNull($testValue);
        $this->assertIsString($testValue);
        $this->assertEquals(self::TEST_STRING, $testValue);
    }

    public function testGet_multiProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;
        $testProp->{self::TEST_KEY_B} = self::TEST_INTEGER;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testValue = $testObj->get(self::TEST_KEY_B);

        $this->assertNotNull($testValue);
        $this->assertIsString($testValue);
        $this->assertEquals(self::TEST_INTEGER, $testValue);

        $testValue = $testObj->get(self::TEST_KEY_A);

        $this->assertNotNull($testValue);
        $this->assertIsString($testValue);
        $this->assertEquals(self::TEST_STRING, $testValue);
    }

    public function testGet_missingKey()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testValue = $testObj->get(self::TEST_KEY_C);

        $this->assertNull($testValue);
    }

    /**
     * @depends testSet_multiProp
     * @depends testGet_multiProp
     */
    public function testSetGet_multiProp()
    {
        $testObj = new UrlDataObject();

        $testObj->set(self::TEST_KEY_A, self::TEST_STRING);
        $testObj->set(self::TEST_KEY_B, self::TEST_INTEGER);

        $this->assertEquals(self::TEST_INTEGER, $testObj->get(self::TEST_KEY_B));
        $this->assertEquals(self::TEST_STRING, $testObj->get(self::TEST_KEY_A));
    }

    public function testEncode_emptyData()
    {
        $testObj = new UrlDataObject();
        $testStr = $testObj->encode();

        $this->assertNull($testStr);
    }

    public function testEncode_singleProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testStr = $testObj->encode();

        $this->assertNotNull($testStr);
        $this->assertStringNotContainsString(self::TEST_KEY_A, $testStr);
        $this->assertStringNotContainsString(self::TEST_STRING, $testStr);
    }

    public function testEncode_multiProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;
        $testProp->{self::TEST_KEY_B} = self::TEST_INTEGER;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testStr = $testObj->encode();

        $this->assertNotNull($testStr);
        $this->assertStringNotContainsString(self::TEST_KEY_A, $testStr);
        $this->assertStringNotContainsString(self::TEST_KEY_B, $testStr);
        $this->assertStringNotContainsString(self::TEST_INTEGER, $testStr);
        $this->assertStringNotContainsString(self::TEST_STRING, $testStr);
    }

    public function testEncode_jsonEncodeError()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        \Brain\Monkey\Functions\when('json_encode')->alias(fn () => false);

        $result = $testObj->encode();

        $this->assertNull($result);
    }

    public function testEncode_deflateError()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        \Brain\Monkey\Functions\when('gzdeflate')->alias(fn () => false);

        $result = $testObj->encode();

        $this->assertNull($result);
    }

    public function testDecode_emptyString()
    {
        $testObj = new UrlDataObject();

        $result = $testObj->decode('');

        $this->assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    public function testDecode_noneBase64String()
    {
        $testObj = new UrlDataObject();

        $result = $testObj->decode(self::NON_BASE64_STR);

        $this->assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    /**
     * @depends testEncode_multiProp
     */
    public function testDecode_jsonDecodeFailure()
    {
        $encStr = $this->buildEncodedString();

        \Brain\Monkey\Functions\when('json_decode')->alias(fn () => false);

        $testObj = new UrlDataObject();
        $result = $testObj->decode($encStr);

        $this->assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    /**
     * @depends testEncode_multiProp
     */
    public function testDecode_base64DecodeFailure()
    {
        $encStr = $this->buildEncodedString();

        \Brain\Monkey\Functions\when('base64_decode')->alias(fn () => false);

        $testObj = new UrlDataObject();
        $result = $testObj->decode($encStr);

        $this->assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    /**
     * @depends testEncode_multiProp
     */
    public function testDecode_gzinflateFailure()
    {
        $encStr = $this->buildEncodedString();

        \Brain\Monkey\Functions\when('gzinflate')->alias(fn () => false);

        $testObj = new UrlDataObject();
        $result = $testObj->decode($encStr);

        $this->assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    /**
     * @depends testEncode_multiProp
     */
    public function testDecode_success()
    {
        $testStr = $this->buildEncodedString();

        $testObj = new UrlDataObject();
        $result = $testObj->decode($testStr);

        $this->assertTrue($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        $this->assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_B});
        $this->assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_A});
    }

    public function testCtor_emptyString()
    {
        $testObj = new UrlDataObject('');

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    public function testCtor_noneBase64String()
    {
        $testObj = new UrlDataObject(self::NON_BASE64_STR);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertEmpty((array)$dataProp);
    }

    public function testCtor_success()
    {
        $encStr = $this->buildEncodedString();

        $testObj = new UrlDataObject($encStr);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        $this->assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_B});
        $this->assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_A});
    }

    public function testSet_chainedCalls()
    {
        $testObj = (new UrlDataObject())
            ->set(self::TEST_KEY_A, self::TEST_INTEGER)
            ->set(self::TEST_KEY_B, self::TEST_STRING);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        $this->assertNotNull($dataProp);
        $this->assertIsObject($dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        $this->assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        $this->assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_A});
        $this->assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_B});
    }
}
