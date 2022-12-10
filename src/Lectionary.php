<?php
/**
 * Lectionary unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

use TMD\OrderOfMass\Models\{CalendarModel,LectlistModel};
use TMD\OrderOfMass\Exceptions\{OomException,ModelException};

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Lectionary reading and processing class.
 */
class Lectionary
{

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Lectionary model
     *
     * @var CalendarModel
     */
    private $calendarModel;

    /**
     * Lectlist model
     *
     * @var LectlistModel
     */
    private $lectListModel;


    /**
     * Store Logger instance and load lectionary JSON.
     *
     * @param Logger        $logger        Logger
     * @param CalendarModel $calendarModel Calendar model
     * @param LectlistModel $lectListModel Lectlist model
     */
    public function __construct(Logger $logger, CalendarModel $calendarModel, LectlistModel $lectListModel)
    {
        $this->logger        = $logger;
        $this->calendarModel = $calendarModel;
        $this->lectListModel = $lectListModel;

    }//end __construct()


    /**
     * Based on the Sunday calendar, return the Sunday abbreviation
     *
     * Example: returns SIOT2 for the 2nd Sunday in Ordinary Time, which in 2022 is on 16th of January
     *
     * @param int $time Unix timestamp
     *
     * @return string
     */
    public function sundayLabel(int $time): string
    {
        $timeTmp = $time;
        while (date('w', $timeTmp) !== '0') {
            $timeTmp += 86400;
        }

        return $this->calendarModel->dateToSunday($timeTmp);

    }//end sundayLabel()


    /**
     * Returns readings from lectionary for the next Sunday after the given date (or that date, if it's Sunday).
     *
     * @param int $time Unix timestamp of the chosen date
     *
     * @return ?array
     */
    public function getReadings($time): ?array
    {
        $cid = $this->sundayLabel($time);
        $yr  = intval(date('Y', $time));
        $mo  = intval(date('m', $time));
        // If it is after the 34th Sunday in Ordinary Time, it's a new year (liturgical).
        if (in_array($cid, ['SOA1', 'SOA2', 'SOA3', 'SOA4', 'SAC1', 'SAC2']) === true && $mo >= 11) {
            $yr++;
        }

        $sc = Helper::sundayCycle($yr);
        return $this->lectListModel->getReadings($cid, $sc);

    }//end getReadings()


}//end class
