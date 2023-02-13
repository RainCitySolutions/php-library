<?php declare(strict_types=1);
namespace RainCity\TestHelper;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * RainCityTestCase base class.
 */
abstract class RainCityTestCase
    extends PHPUnitTestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Runs before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();

        set_error_handler(static function (int $errno, string $errstr) {
            throw new RainCityTestException($errstr, $errno);
        }, E_USER_WARNING);
    }

    /**
     * Runs after each test.
     */
    protected function tearDown(): void {
        restore_error_handler();

        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    protected function generateRandomDateTime(): \DateTime {
        $timestamp = rand(time()-(10 * 365 * 24 * 60 * 60), time()); // a random time between now and 10 years ago

        $testDateTime = new \DateTime();
        $testDateTime->setTimestamp($timestamp);

        return $testDateTime;
    }

}
