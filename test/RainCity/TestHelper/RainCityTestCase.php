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
    }

    /**
     * Runs after each test.
     */
    protected function tearDown(): void {
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
