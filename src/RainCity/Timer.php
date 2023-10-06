<?php declare(strict_types=1);
namespace RainCity;

/**
 * Simple PHP script timing class.
 *
 * Derived from work by Jonathan Jones
 *
 * @author Blair Cooper
 */
class Timer {
    public const NO_TIME_MESSAGE = 'No time to return.';

    private float $start = 0.0;
    private float $pause = 0.0;
    private float $stop = 0.0;
    private float $elapsed = 0.0;
    private float $lapTotalTime = 0.0;
    private array $laps = array();
    private int $count = 1;

    /**
     * Instantiation method.
     *
     * If true is passed then the timer starts immediately. If false is
     * passed or the argument is omitted the timer is not started and start()
     * must be called to start the timer.
     *
     * @param bool $startNow If set to true the timer immediately starts.
     *      Defaults to false.
     */
    public function __construct(bool $startNow = false) {
        if ($startNow) {
            $this->start();
        }
    }

    /**
     * Starts the timer.
     *
     * Resets the timer on each call.
     */
    public function start() {
        $this->start = $this->getMicroTime();
        $this->stop = 0.0;  // reset the stop time
    }

    /**
     * Stops the timer.
     *
     * If the timer has not be started, nothing happens.
     */
    public function stop() {
        // Don't set stop if we haven't started
        if (0.0 !== $this->start) {
            $this->stop = $this->getMicroTime();
        }
    }

    /**
     * Pauses the timer.
     *
     * If the timer has not been started, or has already been stopped,
     * nothing happens.
     */
    public function pause() {
        // Don't set pause if we haven't started or we have already stopped or we are currently paused
        if (0.0 !== $this->start && 0.0 === $this->stop && 0.0 === $this->pause) {
            $this->pause = $this->getMicroTime();
            $this->elapsed += ($this->pause - $this->start);
        }
    }

    /**
     * Resumes the timer after a pause is called.
     *
     * If pause has not been called nothing happens.
     */
    public function resume() {
        if (0.0 !== $this->pause) {
            $this->start = $this->getMicroTime();
            $this->pause = 0.0;
        }
    }

    /**
     * Used to build an array of times for multiple timers, adding a key parameter can be used to name the `lap`
     * @param string $key Used as the key in the kay value pair array.
     */
    public function lap($key = '') {
        $key = ($key === '') ? 'Lap' : $key;
        if (isset($this->start)) {
            $this->stop();
            $this->lapTotalTime += ($this->stop - $this->start);
            $this->laps[$key . ' ' . $this->count] = $this->getLapTime();
            $this->start();
            $this->count++;
        }
    }

    /**
     * Gets the time(s) in a human readable format.
     *
     * If lap() was called, returns an array of times, with an entry for each
     * lap and an entry for the total time.
     *
     * Otherwise returns the total elapsed time.
     *
     * @return string|array The elapsed time or an array of times.
     */
    public function getTime(): string|array
    {
        $this->stop();

        if (!empty($this->laps)) {
            $this->laps['Total'] = $this->timeToString($this->lapTotalTime);
            return $this->laps;
        }

        return $this->timeToString();
    }

    /**
     * Get the time.
     * @return string lap time to lap() function
     */
    private function getLapTime() {
        return $this->timeToString();
    }

    /**
     * Get the microtime.
     * @return float microtime
     */
    private function getMicroTime() {
        return microtime(true);
    }

    /**
     * Convert the time to a readable string for display or logging.
     *
     * @param float $seconds Seconds gathered from the `getTime` function
     *
     * @return string time in a displayable string
     */
    private function timeToString(?float $timeSeconds = null) {
        if (is_null($timeSeconds)) {
            $timeSeconds = ($this->stop - $this->start) + $this->elapsed;
        }
        $timeSeconds = $this->roundMicroTime($timeSeconds);
        
        // Hours?? Just because we can.
        $hours   = floor(fdiv($timeSeconds, 60 * 60));
        $minutes = floor(fmod(fdiv($timeSeconds, 60), 60));
        $seconds = fmod($timeSeconds, 60);
        $seconds = round($seconds, 3, PHP_ROUND_HALF_UP);

        $hours = ($hours == 0) ? '' : $hours . ' hours ';
        $minutes = ($minutes == 0) ? '' : $minutes . ' minutes ';
        $seconds = ($seconds == 0) ? '' : $seconds . ' seconds';

        return ($hours == '' && $minutes == '' && $seconds == '') ?
            self::NO_TIME_MESSAGE :
            $hours . $minutes . $seconds;
    }

    /**
     * Round up the microtime .5 and down .4
     * @param float $microTime Time from `timeToString` function
     * @return float time rounded
     */
    private function roundMicroTime($microTime) {
        return round($microTime, 4, PHP_ROUND_HALF_UP);
    }
}
