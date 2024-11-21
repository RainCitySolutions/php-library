<?php
declare(strict_types=1);
namespace RainCity\TestHelper;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Client\ClientInterface;
use RainCity\DataCache;
use RainCity\Logging\Logger;

/**
 * RainCityTestCase base class.
 */
abstract class RainCityTestCase extends PHPUnitTestCase
{
    private static $orgLoggerClass;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$orgLoggerClass = ReflectionHelper::getClassProperty(Logger::class, 'loggerClazz');
        Logger::setLogger(StubLogger::class);
    }

    /**
     * {@inheritdoc}
     * @see \PHPUnit\Framework\TestCase::tearDownAfterClass()
     */
    public static function tearDownAfterClass(): void
    {
        foreach (self::$tmpFiles as $file) {
            @unlink($file);
        }

        if (isset(self::$orgLoggerClass)) {
            ReflectionHelper::setClassProperty(Logger::class, 'loggerClazz', self::$orgLoggerClass);
        }

        // Cleanup anything DataCache may have stored
        DataCache::uninstall();

        parent::tearDownAfterClass();
    }


    // Adds Mockery expectations to the PHPUnit assertions count.
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private static array $tmpFiles = array();

    protected MockHandler $mockHttpResponses;
    protected array $httpHistory = array();

    /**
     * Runs before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();

        set_error_handler(static function (int $errno, string $errstr) {
            throw new RainCityTestException($errstr, $errno);
        }, E_USER_WARNING);
    }

    /**
     * Runs after each test.
     */
    protected function tearDown(): void
    {
        restore_error_handler();

        $this->resetHttpHistory();

        \Brain\Monkey\tearDown();

        parent::tearDown();
    }

    protected function getTempFile(): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'rcs');
        array_push(self::$tmpFiles, $tmpFile);

        return $tmpFile;
    }


    protected function generateRandomDateTime(): \DateTime
    {
        $timestamp = rand(time()-(10 * 365 * 24 * 60 * 60), time()); // a random time between now and 10 years ago

        $testDateTime = new \DateTime();
        $testDateTime->setTimestamp($timestamp);

        return $testDateTime;
    }

    /**
     * Initialize a Mock HTTP Client using the parameters.
     *
     * {@see \GuzzleHttp\Handler\MockHandler} for additional information on the parameters.}
     * <p>
     * The passed in value must be an array of
     * {@see \Psr\Http\Message\ResponseInterface} objects, Exceptions,callables, or Promises.
     *
     * @param array|null $respQueue The parameters to be passed to the append function, as an indexed array.
     * @param callable|null $onFulfilled Callback to invoke when the return value is fulfilled.
     * @param callable|null $onRejected Callback to invoke when the return value is rejected.
     *
     * @return ClientInterface An HTTP client with the mocked responses queued up.
     */
    protected function initializeMockHttpClient(
        ?array $respQueue=null,
        ?callable $onFulfilled=null,
        ?callable $onRejected=null
        ): ClientInterface
    {
        $this->mockHttpResponses = new MockHandler($respQueue, $onFulfilled, $onRejected);

        $this->resetHttpHistory();

        $handlerStack = HandlerStack::create($this->mockHttpResponses);
        $handlerStack->push(Middleware::history($this->httpHistory));

        return new Client(['handler' => $handlerStack]);
    }

    protected function resetHttpHistory(): void
    {
        $this->httpHistory = array();
    }
}
