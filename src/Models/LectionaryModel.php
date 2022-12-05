<?php
/**
 * Lectionaries/year-xxxx.json Model Unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace TMD\OrderOfMass\Models;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Lectionaries/year-xxxx.json Model
 */
class LectionaryModel extends BaseModel
{


    /**
     * Initialization called at the end of the constructor
     *
     * @return void
     */
    protected function initModel()
    {

    }//end initModel()


    /**
     * Load JSON file
     *
     * @param int $year Lectionary year
     *
     * @return bool
     */
    public function load(int $year): bool
    {
        $ret = $this->loadJson(sprintf('assets/json/lectionaries/year%d.json', $year));
        if ($ret !== true) {
            $ret = $this->buildJson($year);
        }

        return $ret;

    }//end load()


    /**
     * Builds a complete Sundays calendar for the given year.
     *
     * This is necessary to map Sunday abbreviations (e.g. `SIOT2` for Second Sunday in Ordinary Time) to particular dates.
     * There is no easy way to do such mapping on request without having the calendar built first. Really. Check the algorithm
     * and let me know if I'm wrong, I'll be more than happy for some improvement suggestions.
     *
     * Luckily, it is necessary to build calendar only once for each year, because it is stored afterwards and every successive
     * call reads the calendar, which was already built before.
     *
     * @param int $year Year
     *
     * @return bool
     *
     * @see https://www.omnicalculator.com/everyday-life/easter
     * @see https://www.omnicalculator.com/everyday-life/moon-phase
     * @see https://catholic-resources.org/Lectionary/
     */
    private function buildJson(int $year): bool
    {
        $this->jsonContent = [];

        // OK, let's do this...
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
        // Known full moon date close to 12 pm.
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
        $this->jsonContent[$dt2->format('d.m.Y')] = "FOTBOTL";
        $dt2->add($owi);

        // Add Ordinary Time Sundays until before Ash Wednesday.
        while ($dt2->diff($ash)->format('%R') === '+') {
            $this->jsonContent[$dt2->format('d.m.Y')] = "SIOT${sund}";
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

        // Go back four Sundays.
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
            $this->jsonContent[$dt2->format('d.m.Y')] = "SOL${i}";
            $dt2->add($owi);
        }

        // Add Palm Sunday.
        $this->jsonContent[$dt2->format('d.m.Y')] = "PaS";
        $dt2->add($owi);

        // Add Easter Sunday.
        $this->jsonContent[$dt2->format('d.m.Y')] = "ES";
        $dt2->add($owi);

        // Add 6 Sundays until before Pentecost Sunday.
        for ($i = 2; $i <= 7; $i++) {
            $this->jsonContent[$dt2->format('d.m.Y')] = "SOE${i}";
            $dt2->add($owi);
        }

        // Fill Sundays until before Advent.
        while ($dt2->diff($ce)->format('%R') === '+') {
            $this->jsonContent[$dt2->format('d.m.Y')] = "SIOT${sund}";
            $dt2->add($owi);
            $sund++;
        }

        // Add 4 Advent Sundays.
        for ($i = 1; $i <= 4; $i++) {
            $this->jsonContent[$dt2->format('d.m.Y')] = "SOA${i}";
            $dt2->add($owi);
        }

        // Add 2 Sundays after Christmas.
        if ($dt2->format('d') === '25') {
            $this->jsonContent[$dt2->format('d.m.Y')] = "SAC1B";
            $dt2->add($owi);
            $this->jsonContent[$dt2->format('d.m.Y')] = "SAC2";
            $dt2->add($owi);
        } else {
            for ($i = 1; $i <= 2; $i++) {
                $this->jsonContent[$dt2->format('d.m.Y')] = "SAC${i}";
                $dt2->add($owi);
            }
        }

        return $this->saveJson();

    }//end buildJson()


    /**
     * Hello
     *
     * @param int $time Timestamp
     *
     * @return ?string
     */
    public function dateToSunday(int $time): ?string
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        $date = date('d.m.Y', $time);
        if (array_key_exists($date, $this->jsonContent) !== true) {
            return null;
        }

        return $this->jsonContent[$date];

    }//end dateToSunday()


    /**
     * Hello
     *
     * @param string $sunday Sunday abbreviation
     *
     * @return ?int
     */
    public function sundayToDate(string $sunday): ?int
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        $key = array_search($sunday, $this->jsonContent);
        if ($key === false || is_int($key) !== true) {
            return null;
        }

        return $key;

    }//end sundayToDate()


}//end class
