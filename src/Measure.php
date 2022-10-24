<?php
/**
 * Performance measurement
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\OrderOfMass;

/**
 * Performance measurement class
 */
class Measure
{

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * Result of {@see getrusage()} at the beginning
     *
     * @var array
     */
    private $startTime = [];


    /**
     * Constructor
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

    }//end __construct()


    /**
     * Starts measuring
     *
     * @return void
     */
    public function start()
    {
        $this->startTime = getrusage();

    }//end start()


    /**
     * Returns the runtime in milliseconds
     *
     * @param mixed $ru    End time
     * @param mixed $rus   Start time
     * @param mixed $index `utime` for user time, `stime` for system time
     *
     * @return int
     */
    private function runTime($ru, $rus, $index)
    {
        $start = ($rus["ru_$index.tv_sec"] * 1000 + intval(($rus["ru_$index.tv_usec"] / 1000)));
        $end   = ($ru["ru_$index.tv_sec"] * 1000 + intval(($ru["ru_$index.tv_usec"] / 1000)));
        return ($end - $start);

    }//end runTime()


    /**
     * Finishes measurement and returns time/memory measurement info
     *
     * @return array<string, int>
     */
    public function finish()
    {
        $endTime = getrusage();
        return [
            'runtime'        => ($this->runTime($endTime, $this->startTime, "utime") + $this->runTime($endTime, $this->startTime, "stime")),
            'mempeakreal'    => \memory_get_peak_usage(true),
            'memusereal'     => \memory_get_usage(true),
            'mempeaknonreal' => \memory_get_peak_usage(false),
            'memusenonreal'  => \memory_get_usage(false),
        ];

    }//end finish()


}//end class
