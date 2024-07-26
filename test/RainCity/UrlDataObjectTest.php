<?php
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(\RainCity\UrlDataObject::class)]
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

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_A, $dataProp);

        self::assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_A});
    }

    public function testSet_multiProp()
    {
        $testObj = new UrlDataObject();

        $testObj->set(self::TEST_KEY_A, self::TEST_INTEGER);
        $testObj->set(self::TEST_KEY_B, self::TEST_STRING);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        self::assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_A});
        self::assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_B});
    }

    public function testGet_singleProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testValue = $testObj->get(self::TEST_KEY_A);

        self::assertNotNull($testValue);
        self::assertIsString($testValue);
        self::assertEquals(self::TEST_STRING, $testValue);
    }

    public function testGet_multiProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;
        $testProp->{self::TEST_KEY_B} = self::TEST_INTEGER;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testValue = $testObj->get(self::TEST_KEY_B);

        self::assertNotNull($testValue);
        self::assertIsString($testValue);
        self::assertEquals(self::TEST_INTEGER, $testValue);

        $testValue = $testObj->get(self::TEST_KEY_A);

        self::assertNotNull($testValue);
        self::assertIsString($testValue);
        self::assertEquals(self::TEST_STRING, $testValue);
    }

    public function testGet_missingKey()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testValue = $testObj->get(self::TEST_KEY_C);

        self::assertNull($testValue);
    }

    #[Depends('testSet_multiProp')]
    #[Depends('testGet_multiProp')]
    public function testSetGet_multiProp()
    {
        $testObj = new UrlDataObject();

        $testObj->set(self::TEST_KEY_A, self::TEST_STRING);
        $testObj->set(self::TEST_KEY_B, self::TEST_INTEGER);

        self::assertEquals(self::TEST_INTEGER, $testObj->get(self::TEST_KEY_B));
        self::assertEquals(self::TEST_STRING, $testObj->get(self::TEST_KEY_A));
    }

    public function testEncode_emptyData()
    {
        $testObj = new UrlDataObject();
        $testStr = $testObj->encode();

        self::assertNull($testStr);
    }

    public function testEncode_singleProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testStr = $testObj->encode();

        self::assertNotNull($testStr);
        self::assertStringNotContainsString(self::TEST_KEY_A, $testStr);
        self::assertStringNotContainsString(self::TEST_STRING, $testStr);
    }

    public function testEncode_multiProp()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;
        $testProp->{self::TEST_KEY_B} = self::TEST_INTEGER;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        $testStr = $testObj->encode();

        self::assertNotNull($testStr);
        self::assertStringNotContainsString(self::TEST_KEY_A, $testStr);
        self::assertStringNotContainsString(self::TEST_KEY_B, $testStr);
        self::assertStringNotContainsString(self::TEST_INTEGER, $testStr);
        self::assertStringNotContainsString(self::TEST_STRING, $testStr);
    }

    public function testEncode_jsonEncodeError()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        \Brain\Monkey\Functions\when('json_encode')->alias(fn () => false);

        $result = $testObj->encode();

        self::assertNull($result);
    }

    public function testEncode_deflateError()
    {
        $testObj = new UrlDataObject();
        $testProp = new \stdClass();

        $testProp->{self::TEST_KEY_A} = self::TEST_STRING;

        ReflectionHelper::setObjectProperty(UrlDataObject::class, 'data', $testProp, $testObj);

        \Brain\Monkey\Functions\when('gzdeflate')->alias(fn () => false);

        $result = $testObj->encode();

        self::assertNull($result);
    }

    public function testDecode_emptyString()
    {
        $testObj = new UrlDataObject();

        $result = $testObj->decode('');

        self::assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    public function testDecode_noneBase64String()
    {
        $testObj = new UrlDataObject();

        $result = $testObj->decode(self::NON_BASE64_STR);

        self::assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    #[Depends('testEncode_multiProp')]
    public function testDecode_jsonDecodeFailure()
    {
        $encStr = $this->buildEncodedString();

        \Brain\Monkey\Functions\when('json_decode')->alias(fn () => false);

        $testObj = new UrlDataObject();
        $result = $testObj->decode($encStr);

        self::assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    #[Depends('testEncode_multiProp')]
    public function testDecode_base64DecodeFailure()
    {
        $encStr = $this->buildEncodedString();

        \Brain\Monkey\Functions\when('base64_decode')->alias(fn () => false);

        $testObj = new UrlDataObject();
        $result = $testObj->decode($encStr);

        self::assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    #[Depends('testEncode_multiProp')]
    public function testDecode_gzinflateFailure()
    {
        $encStr = $this->buildEncodedString();

        \Brain\Monkey\Functions\when('gzinflate')->alias(fn () => false);

        $testObj = new UrlDataObject();
        $result = $testObj->decode($encStr);

        self::assertFalse($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    #[Depends('testEncode_multiProp')]
    public function testDecode_success()
    {
        $testStr = $this->buildEncodedString();

        $testObj = new UrlDataObject();
        $result = $testObj->decode($testStr);

        self::assertTrue($result);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        self::assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_B});
        self::assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_A});
    }

    public function testCtor_emptyString()
    {
        $testObj = new UrlDataObject('');

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    public function testCtor_noneBase64String()
    {
        $testObj = new UrlDataObject(self::NON_BASE64_STR);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertEmpty((array)$dataProp);
    }

    public function testCtor_success()
    {
        $encStr = $this->buildEncodedString();

        $testObj = new UrlDataObject($encStr);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        self::assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_B});
        self::assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_A});
    }

    public function testSet_chainedCalls()
    {
        $testObj = (new UrlDataObject())
            ->set(self::TEST_KEY_A, self::TEST_INTEGER)
            ->set(self::TEST_KEY_B, self::TEST_STRING);

        $dataProp = ReflectionHelper::getObjectProperty(UrlDataObject::class, 'data', $testObj);

        self::assertNotNull($dataProp);
        self::assertIsObject($dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_A, $dataProp);
        self::assertObjectHasProperty(self::TEST_KEY_B, $dataProp);

        self::assertEquals(self::TEST_INTEGER, $dataProp->{self::TEST_KEY_A});
        self::assertEquals(self::TEST_STRING, $dataProp->{self::TEST_KEY_B});
    }
}
