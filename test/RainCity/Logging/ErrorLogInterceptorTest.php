<?php
declare(strict_types = 1);
namespace RainCity\Logging;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RainCity\TestHelper\ReflectionHelper;

#[CoversClass(\RainCity\Logging\ErrorLogInterceptor::class)]
class ErrorLogInterceptorTest extends TestCase
{
    private mixed $origHandler;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();
        ReflectionHelper::setClassProperty(ErrorLogInterceptor::class, 'instance', null, true);

        $this->origHandler = set_error_handler(function () { return false; });
    }

    protected function tearDown(): void
    {
        $testObj = ReflectionHelper::getClassProperty(ErrorLogInterceptor::class, 'instance');

        if (isset($testObj)) {
            $orgHandler = ReflectionHelper::getObjectProperty(ErrorLogInterceptor::class, 'origHandler', $testObj);

            if (isset($orgHandler)) {
                restore_error_handler();
            }
        }

        restore_error_handler();
    }

    public function testInstance_noErrors(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ErrorLogInterceptor::instance();
    }

    public function testInstance_nonArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ErrorLogInterceptor::instance('foobar');
    }

    public function testInstance_noValidErrors(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ErrorLogInterceptor::instance(['foo' => 'bar']);
    }

    public function testInstance(): void
    {
        $errorsToIgnore = [
            E_NOTICE => [
                '_load_textdomain_just_in_time'
            ]
        ];

        $testObj = ErrorLogInterceptor::instance($errorsToIgnore);

        $testErrors = ReflectionHelper::getObjectProperty(ErrorLogInterceptor::class, 'ignoreErrors', $testObj);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($errorsToIgnore, $testErrors, []);
    }

    public function testInstance_extraKeys(): void
    {
        $realErrorsToIgnore = [
            E_NOTICE => [
                '_load_textdomain_just_in_time'
            ]
        ];
        $extraErrorsToIgnore = [
            'junkEntry' => [
                'Some junk message'
                ],
            1111 => [
                'Some other junk message'
            ]
        ] +
        $realErrorsToIgnore
        ;

        $testObj = ErrorLogInterceptor::instance($extraErrorsToIgnore);

        $testErrors = ReflectionHelper::getObjectProperty(ErrorLogInterceptor::class, 'ignoreErrors', $testObj);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($realErrorsToIgnore, $testErrors, []);
    }

    public function testHandler(): void
    {
        $testErrNum = E_NOTICE;
        $testErrStr = 'Function _load_textdomain_just_in_time was called <strong>incorrectly</strong>. Translation loading for the <code>acf</code> domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the <code>init</code> action or later. Please see <a href="https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/">Debugging in WordPress</a> for more information. (This message was added in version 6.7.0.)';

        $errorsToIgnore = [
            $testErrNum => [
                '_load_textdomain_just_in_time'
            ]
        ];

        $testObj = ErrorLogInterceptor::instance($errorsToIgnore);

        $testErrors = ReflectionHelper::getObjectProperty(ErrorLogInterceptor::class, 'ignoreErrors', $testObj);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($errorsToIgnore, $testErrors, []);

        $this->assertTrue($testObj->handler($testErrNum, $testErrStr, __FILE__, __LINE__));
        $this->assertFalse($testObj->handler($testErrNum, 'Some other error message', __FILE__, __LINE__));
    }
}
