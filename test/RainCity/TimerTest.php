<?php declare(strict_types=1);
namespace RainCity;

use RainCity\TestHelper\RainCityTestCase;
use RainCity\TestHelper\ReflectionHelper;

/**
 * @covers \RainCity\Timer
 *
 */
class TimerTest extends RainCityTestCase
{
    private const SLEEP_TIME_MICROSECONDS = 500000;
    private const SLEEP_TIME_SECONDS = self::SLEEP_TIME_MICROSECONDS / 1000000;
    private const START_PROPERTY = 'start';
    private const STOP_PROPERTY = 'stop';
    private const PAUSE_PROPERTY = 'pause';
    private const ELAPSED_PROPERTY = 'elapsed';
    private const LAP_KEY = 'lapKey';
    private const SECONDS_TIME_PATTERN = '/^\d\.?\d{0,3} seconds$/';

    public function testCtor_noParam()
    {
        $timer = new Timer();

        $startVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        $this->assertEquals(floatval(0), $startVal);
    }

    public function testCtor_doNotStart()
    {
        $timer = new Timer(false);

        $startVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        $this->assertEquals(floatval(0), $startVal);
    }

    public function testCtor_doStart()
    {
        $timer = new Timer(true);

        $startVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        $this->assertNotEquals(floatval(0), $startVal);
    }

    public function testStart()
    {
        $timer = new Timer(false);

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::START_PROPERTY));

        $timer->start();

        $this->assertNotEquals(floatval(0), $this->getTimerProperty($timer, self::START_PROPERTY));
        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::STOP_PROPERTY, $timer));
    }

    public function testStop_notStarted()
    {
        $timer = new Timer(false);

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::START_PROPERTY));

        $timer->stop();

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::STOP_PROPERTY));
    }

    public function testStop_started()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->stop();

        $startVal = $this->getTimerProperty($timer, self::START_PROPERTY);
        $stopVal = $this->getTimerProperty($timer, self::STOP_PROPERTY);

        $this->assertNotEquals(floatval(0), $startVal);
        $this->assertNotEquals(floatval(0), $stopVal);
        $this->assertGreaterThan($startVal, $stopVal);
        $this->assertGreaterThan(self::SLEEP_TIME_SECONDS, $stopVal - $startVal);
        $this->assertLessThan(self::SLEEP_TIME_SECONDS * 2, $stopVal - $startVal);
    }

    public function testStart_restart()
    {
        $timer = new Timer(false);

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::START_PROPERTY));

        $timer->start();

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->stop();
        $timer->start();

        $this->assertNotEquals(floatval(0), $this->getTimerProperty($timer, self::START_PROPERTY));
        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::STOP_PROPERTY));
    }

    public function testPause_notStarted()
    {
        $timer = new Timer(false);

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::START_PROPERTY));

        $timer->pause();

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::STOP_PROPERTY));
        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::PAUSE_PROPERTY));
        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::ELAPSED_PROPERTY));
    }

    public function testPause_started()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->pause();

        $startVal = $this->getTimerProperty($timer, self::START_PROPERTY);
        $pauseVal = $this->getTimerProperty($timer, self::PAUSE_PROPERTY);
        $elapsedVal = $this->getTimerProperty($timer, self::ELAPSED_PROPERTY);

        $this->assertNotEquals(floatval(0), $startVal);
        $this->assertNotEquals(floatval(0), $pauseVal);
        $this->assertNotEquals(floatval(0), $elapsedVal);

        $this->assertGreaterThan($startVal, $pauseVal);

        // Time between start and pause should be just over 1 second
        $this->assertGreaterThan(self::SLEEP_TIME_SECONDS, $elapsedVal);
        $this->assertLessThan(self::SLEEP_TIME_SECONDS * 2, $elapsedVal);
    }

    public function testPause_alreadyStopped()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->stop();
        $timer->pause();

        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::PAUSE_PROPERTY));
        $this->assertEquals(floatval(0), $this->getTimerProperty($timer, self::ELAPSED_PROPERTY));
    }

    public function testPause_alreadyPaused()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->pause();

        $orgPauseVal = $this->getTimerProperty($timer, self::PAUSE_PROPERTY);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->pause();

        $curPauseVal = $this->getTimerProperty($timer, self::PAUSE_PROPERTY);

        $this->assertNotEquals(floatval(0), $orgPauseVal);
        $this->assertNotEquals(floatval(0), $curPauseVal);

        $this->assertEquals($orgPauseVal, $curPauseVal);
    }

    public function testResume_notStartedNotPaused()
    {
        $timer = new Timer(false);

        $orgStartVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->resume();

        $resumeStartVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        $this->assertEquals(floatval(0), $orgStartVal);
        $this->assertEquals(floatval(0), $resumeStartVal);
    }

    public function testResume_startedNotPaused()
    {
        $timer = new Timer(true);

        $orgStartVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->resume();

        $resumeStartVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        $this->assertNotEquals(floatval(0), $orgStartVal);
        $this->assertNotEquals(floatval(0), $resumeStartVal);
        $this->assertEquals($orgStartVal, $resumeStartVal);
    }

    public function testPauseResumeStop()
    {
        $timer = new Timer(true);

        $orgStartVal = $this->getTimerProperty($timer, self::START_PROPERTY);

        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->pause();

        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->resume();

        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->stop();

        $startVal = $this->getTimerProperty($timer, self::START_PROPERTY);
        $stopVal = $this->getTimerProperty($timer, self::STOP_PROPERTY);
        $elapsedVal = $this->getTimerProperty($timer, self::ELAPSED_PROPERTY);

        $startStopDiff = $stopVal - $startVal;

        $this->assertNotEquals(floatval(0), $orgStartVal);
        $this->assertNotEquals(floatval(0), $startVal);
        $this->assertNotEquals(floatval(0), $stopVal);
        $this->assertNotEquals(floatval(0), $elapsedVal);

        $this->assertGreaterThan($startVal, $stopVal);

        // Time between initial start and pause should be just over SLEEP_TIME second
        $this->assertGreaterThan(self::SLEEP_TIME_SECONDS, $elapsedVal);
        $this->assertLessThan(self::SLEEP_TIME_SECONDS * 2, $elapsedVal);

        // Time between resume and stop should be just over SLEEP_TIME second
        $this->assertGreaterThan(self::SLEEP_TIME_SECONDS, $startStopDiff);
        $this->assertLessThan(self::SLEEP_TIME_SECONDS * 2, $startStopDiff);

        // Total elapsed time should be just over SLEEP_TIME * 2 seconds
        $this->assertGreaterThan(self::SLEEP_TIME_SECONDS * 2, $startStopDiff + $elapsedVal);
        $this->assertLessThan(self::SLEEP_TIME_SECONDS * 3, $startStopDiff + $elapsedVal);
    }

    public function testTimeToString_noTime()
    {
        $timer = new Timer();

        $result = ReflectionHelper::invokeObjectMethod(Timer::class, $timer, 'timeToString', 0.0);

        $this->assertEquals(Timer::NO_TIME_MESSAGE, $result);
    }

    public function testTimeToString_mills()
    {
        $timer = new Timer();

        $testTime = 0.215;

        $result = ReflectionHelper::invokeObjectMethod(Timer::class, $timer, 'timeToString', $testTime);

        $this->assertMatchesRegularExpression('/^0.215 seconds$/', $result);
    }

    public function testTimeToString_seconds()
    {
        $timer = new Timer();

        $testTime = 5.0;

        $result = ReflectionHelper::invokeObjectMethod(Timer::class, $timer, 'timeToString', $testTime);

        $this->assertMatchesRegularExpression('/^5 seconds$/', $result);
    }

    public function testTimeToString_minutes()
    {
        $timer = new Timer();

        $testTime = (3.0 * 60) + 24.0;  // 3 minutes, 24 seconds

        $result = ReflectionHelper::invokeObjectMethod(Timer::class, $timer, 'timeToString', $testTime);

        $this->assertMatchesRegularExpression('/^3 minutes 24 seconds$/', $result);
    }

    public function testTimeToString_hours()
    {
        $timer = new Timer();

        $testTime = (5.0 * 3600) + (27.0 * 60) + 52.671;  // 5 hours 27 minutes 52.671 seconds

        $result = ReflectionHelper::invokeObjectMethod(Timer::class, $timer, 'timeToString', $testTime);

        $this->assertMatchesRegularExpression('/^5 hours 27 minutes 52.671 seconds$/', $result);
    }

//     /**
//      * Used to build an array of times for multiple timers, adding a key parameter can be used to name the `lap`
//      * @param string $key Used as the key in the kay value pair array.
//      */
//     public function lap($key = '') {
//         $key = ($key === '') ? 'Lap' : $key;
//         if (isset($this->start)) {
//             $this->stop();
//             $this->lapTotalTime += ($this->stop - $this->start);
//             $this->laps[$key . ' ' . $this->count] = $this->getLapTime();
//             $this->start();
//             $this->count++;
//         }
//     }


    public function testGetTime_explicitStop()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);
        usleep(self::SLEEP_TIME_MICROSECONDS);

        $timer->stop();

        $result = $timer->getTime();

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression(self::SECONDS_TIME_PATTERN, $result);
    }

    public function testGetTime_impliedStop()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);

        $result = $timer->getTime();

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression(self::SECONDS_TIME_PATTERN, $result);
    }

    public function testGetTime_laps()
    {
        $timer = new Timer(true);

        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->lap(self::LAP_KEY);
        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->lap(self::LAP_KEY);
        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->lap(self::LAP_KEY);
        usleep(self::SLEEP_TIME_MICROSECONDS);
        $timer->stop();

        $result = $timer->getTime();

        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        foreach($result as $key => $lap) {
            $this->assertMatchesRegularExpression('/^('.self::LAP_KEY.' \d|Total)$/', $key);
            $this->assertMatchesRegularExpression(self::SECONDS_TIME_PATTERN, $lap);
        }
    }

    private function getTimerProperty(Timer $timer, string $property): float
    {
        return ReflectionHelper::getObjectProperty(Timer::class, $property, $timer);
    }
}
