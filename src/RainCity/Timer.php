<?php declare(strict_types=1);
namespace RainCity;

/**
 * Simple PHP script timing class.
 * @author Jonathan Jones
 */
class Timer {
    private float $start;
    private float $pause;
    private float $stop;
    private float $elapsed = 0;
    private array $laps = array();
    private int $count = 1;
    private float $lapTotalTime = 0;

    /**
     * Instantiation method, if `start` is declared then the timing will
     * start, else `start()` needs to be called.
     *
     * @param string $start Only acceptable string currently is `start`.
     */
    public function __construct($start = '') {
        ('start' === strtolower($start)) ? $this->start() : null;
    }

    /**
     * Starts the timer. Resets on each call.
     */
    public function start() {
        $this->start = $this->getMicroTime();
        unset($this->stop);    // reset the stop time
    }

    /**
     * Stops the timer.
     */
    public function stop() {
        $this->stop = $this->getMicroTime();
    }

    /**
     * Pauses the timer.
     */
    public function pause() {
        $this->pause = $this->getMicroTime();
        $this->elapsed += ($this->pause - $this->start);
    }

    /**
     * Resumes the timer after a pause is called.
     */
    public function resume() {
        $this->start = $this->getMicroTime();
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
     * Gets the time for the user after processing the time through private functions.
     * @return string Time
     */
    public function getTime() {
        if (!isset($this->stop)) {
            $this->stop = $this->getMicroTime();
        }
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
     * @param float $seconds Seconds gathered from the `getTime` function
     * @return string time in a displayable string
     */
    private function timeToString($seconds = '') {
        if ($seconds === '') {
            $seconds = ($this->stop - $this->start) + $this->elapsed;
        }
        $seconds = $this->roundMicroTime($seconds);
        // Hours?? Just because we can.
        $hours = floor($seconds / (60 * 60));
        $divisorForMinutes = $seconds % (60 * 60);
        $minutes = floor($divisorForMinutes / 60);
        $hours = ($hours == 0 || $hours == '0') ? '' : $hours . ' hours ';
        $minutes = ($minutes == 0 || $minutes == '0') ? '' : $minutes . ' minutes ';
        $seconds = ($seconds == 0 || $seconds == '0') ? '' : $seconds . ' seconds ';
        return ($hours == '' && $minutes == '' && $seconds == '') ? 'No time to return.' : $hours . $minutes . $seconds;
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
