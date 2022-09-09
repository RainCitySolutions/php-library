<?php
namespace RainCity;

/**
 * Simple PHP script timing class.
 * @author Jonathan Jones
 */
class Timer {
    private $_start, $_pause, $_stop, $_elapsed;
    private $_laps = array();
    private $_count = 1;
    private $_lapTotalTime = 0;

    /**
     * Instantiation method, if `start` is declared then the timing will
     * start, else `start()` needs to be called.
     *
     * @param string $start Only acceptable string currently is `start`.
     */
    public function __construct($start = '') {
        ('start' === strtolower($start)) ? $this->start() : NULL;
    }

    /**
     * Starts the timer. Resets on each call.
     */
    public function start() {
        $this->_start = $this->getMicroTime();
        unset($this->_stop);    // reset the stop time
    }

    /**
     * Stops the timer.
     */
    public function stop() {
        $this->_stop = $this->getMicroTime();
    }

    /**
     * Pauses the timer.
     */
    public function pause() {
        $this->_pause = $this->getMicroTime();
        $this->_elapsed += ($this->_pause - $this->_start);
    }

    /**
     * Resumes the timer after a pause is called.
     */
    public function resume() {
        $this->_start = $this->getMicroTime();
    }

    /**
     * Used to build an array of times for multiple timers, adding a key parameter can be used to name the `lap`
     * @param string $key Used as the key in the kay value pair array.
     */
    public function lap($key = '') {
        $key = ($key === '') ? 'Lap' : $key;
        if (isset($this->_start)) {
            $this->stop();
            $this->_lapTotalTime += ($this->_stop - $this->_start);
            $this->_laps[$key . ' ' . $this->_count] = $this->getLapTime();
            $this->start();
            $this->_count++;
        }
    }

    /**
     * Gets the time for the user after processing the time through private functions.
     * @return string Time
     */
    public function getTime() {
        if (!isset($this->_stop)) {
            $this->_stop = $this->getMicroTime();
        }
        if (!empty($this->_laps)) {
            $this->_laps['Total'] = $this->timeToString($this->_lapTotalTime);
            return $this->_laps;
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
//         list($usec, $sec) = explode(' ', microtime());
//         return ((float) $usec + (float) $sec);
    }

    /**
     * Convert the time to a readable string for display or logging.
     * @param float $seconds Seconds gathered from the `getTime` function
     * @return string time in a displayable string
     */
    private function timeToString($seconds = '') {
        if ($seconds === '') {
            $seconds = ($this->_stop - $this->_start) + $this->_elapsed;
        }
        $seconds = $this->roundMicroTime($seconds);
        // Hours?? Just because we can.
        $hours = floor($seconds / (60 * 60));
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
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
