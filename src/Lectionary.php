<?php
/**
 * Support class for lectionary for the Order of Mass app
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\OrderOfMass;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Support class for lectionary for the Order of Mass app
 */
class Lectionary
{

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * Lectionary
     *
     * @var array
     */
    private $lectionary = [];


    /**
     * Prepare calendar of Sundays
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger     = $logger;
        $this->lectionary = Helper::loadJson('assets/json/lectlist.json');

    }//end __construct()


    /**
     * Based on the Sunday calendar, return the Sunday label
     *
     * Example: returns SIOT2 for the 2nd Sunday in Ordinary Time, which in 2022 is on 16th of January
     *
     * @param int $time Unix timestamp
     *
     * @return string|null
     */
    public function sundayLabel($time)
    {
        $time = time();
        while (date('w', $time) !== '0') {
            $time += 86400;
        }

        $date = date('d.m.Y', $time);

        $calendar = $this->calendar(intval(date('Y', $time)));
        foreach ($calendar as $dt) {
            foreach ($dt as $dat => $id) {
                if ($dat === $date) {
                    return $id;
                }
            }
        }

        return null;

    }//end sundayLabel()


    /**
     * Returns readings from lectionary for the next Sunday after the given date (or that date, if it's Sunday)
     *
     * @param int $time Unix timestamp
     *
     * @return array
     */
    public function getReadings($time)
    {
        $cid = $this->sundayLabel($time);
        if ($cid === null) {
            return;
        }

        if (array_key_exists($cid, $this->lectionary) !== true) {
            return;
        }

        $sc = Helper::sundayCycle(intval(date('Y', $time)));
        if (array_key_exists($sc, $this->lectionary[$cid]) !== true) {
            return;
        }

        return $this->lectionary[$cid][$sc];

    }//end getReadings()


    /**
     * Builds a complete Sundays calendar for the given year
     *
     * @param int $year Year
     *
     * @return array
     *
     * @see https://www.omnicalculator.com/everyday-life/easter
     * @see https://www.omnicalculator.com/everyday-life/moon-phase
     * @see https://catholic-resources.org/Lectionary/
     */
    private function calendar($year)
    {
        $calFile  = 'assets/json/lectionaries/year'.$year.'.json';
        $calendar = Helper::loadJson($calFile);
        if (count($calendar) > 0) {
            return $calendar;
        }

        // First compute the first Sunday after January 6.
        $dt  = new \DateTime($year.'-01-06');
        $odi = new \DateInterval('P1D');
        $dt2 = $dt->add($odi);
        $cnt = 0;
        while ($dt2->format('w') !== '0' && $cnt < 8) {
            $dt2->add($odi);
        }

        // Now we need to know the date of Ash Wednesday.
        $equinox = new \DateTime($year.'-03-21');
        // Current year's spring equinox.
        $fms = new \DateTime('2000-01-06');
        // Known full moon date.
        $dftmp = intval($fms->diff($equinox)->format('%a'));
        // Days between.
        while ($dftmp >= 29.53058770576) {
            // Repeatedly subtract one month.
            $dftmp -= 29.53058770576;
        }

        if ($dftmp > 15.765294) {
            // If we are in the second half of the cycle, subtract one more month.
            $dftmp -= 29.53058770576;
        }

        $dtfm = 0;
        // Compute days until next full moon.
        while ($dftmp <= 14.765294) {
            $dtfm++;
            $dftmp++;
        }

        $ash = $equinox->add(new \DateInterval("P${dtfm}D"));
        // Date of first full moon after spring equinox.
        while ($ash->format('w') !== '0') {
            // Find first sunday after that full moon.
            $ash = $ash->add($odi);
        }

        $ash->sub(new \DateInterval("P46D"));
        // Subtract 46 days (that is Ash Wednesday).
        $sund = 2;
        $owi  = new \DateInterval('P1W');

        // Add Feast of the Baptism of the Lord.
        $calendar[] = [$dt2->format('d.m.Y') => "FOTBOTL"];
        $dt2->add($owi);

        // Add Ordinary Time Sundays until before Ash Wednesday.
        while ($dt2->diff($ash)->format('%R') === '+') {
            $calendar[] = [$dt2->format('d.m.Y') => "SIOT${sund}"];
            $dt2->add($owi);
            $sund++;
        }

        // Now we need to know the beginning of Advent.
        $ce = new \DateTime($year.'-12-24');
        // Christmas Eve.
        $sbce = 0;
        if ($ce->format('w') === '0') {
            $sbce = 1;
        }

        while ($sbce < 4) {
            $ce->sub($odi);
            if ($ce->format('w') === '0') {
                $sbce++;
            }
        }

        $ce->sub($odi);

        // To skip or not to skip? We need to finish with 34th Sunday in O.T.
        $dt3   = clone $dt2;
        $sund3 = $sund;
        for ($i = 1; $i <= 13; $i++) {
            $dt3->add($owi);
        }

        while ($dt3->diff($ce)->format('%R') === '+') {
            $dt3->add($owi);
            $sund3++;
        }

        if ($sund3 < 35) {
            $sund++;
        }

        // Add 5 Lenten Sundays.
        for ($i = 1; $i <= 5; $i++) {
            $calendar[] = [$dt2->format('d.m.Y') => "SOL${i}"];
            $dt2->add($owi);
        }

        // Add Palm Sunday.
        $calendar[] = [$dt2->format('d.m.Y') => "PaS"];
        $dt2->add($owi);

        // Add Easter Sunday.
        $calendar[] = [$dt2->format('d.m.Y') => "ES"];
        $dt2->add($owi);

        // Add 6 Sundays until before Pentecost Sunday.
        for ($i = 2; $i <= 7; $i++) {
            $calendar[] = [$dt2->format('d.m.Y') => "SOE${i}"];
            $dt2->add($owi);
        }

        // Fill Sundays until before Advent.
        while ($dt2->diff($ce)->format('%R') === '+') {
            $calendar[] = [$dt2->format('d.m.Y') => "SIOT${sund}"];
            $dt2->add($owi);
            $sund++;
        }

        // Add 4 Advent Sundays.
        for ($i = 1; $i <= 4; $i++) {
            $calendar[] = [$dt2->format('d.m.Y') => "SOA${i}"];
            $dt2->add($owi);
        }

        // Add 2 Sundays after Christmas.
        if ($dt2->format('d') === '25') {
            $calendar[] = [$dt2->format('d.m.Y') => "SAC1B"];
            $dt2->add($owi);
            $calendar[] = [$dt2->format('d.m.Y') => "SAC2"];
            $dt2->add($owi);
        } else {
            for ($i = 1; $i <= 2; $i++) {
                $calendar[] = [$dt2->format('d.m.Y') => "SAC${i}"];
                $dt2->add($owi);
            }
        }

        file_put_contents(__DIR__.'/../'.$calFile, json_encode($calendar));

        return $calendar;

    }//end calendar()


}//end class