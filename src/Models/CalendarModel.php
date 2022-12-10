<?php
/**
 * Calendars/year-xxxx.json Model Unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace TMD\OrderOfMass\Models;

use TMD\OrderOfMass\Exceptions\ModelException;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Calendars/year-xxxx.json Model
 */
class CalendarModel extends BaseModel
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
     * @param int $liturgicalYear Liturgical year (calendar year until the day before 1st Sunday of Advent, calendar year+1 afterwards)
     *
     * @return void
     */
    public function load(int $liturgicalYear): void
    {
        try {
            $this->loadJson(sprintf('assets/json/calendars/year%d.json', $liturgicalYear));
        } catch (ModelException $e) {
            if ($this->jsonFilename === '') {
                throw $e;
            } else {
                $this->buildJson($liturgicalYear);
            }
        }

        $this->checkAndSanitize();

    }//end load()


    /**
     * Check JSON structure and sanitize its content.
     *
     * 1. Check the decoded JSON structure and allow only known elements
     * 2. Sanitize all values (strings, array keys/values, object properties/values)
     *
     * @return void
     */
    private function checkAndSanitize(): void
    {
        if (is_array($this->jsonContent) !== true) {
            $this->logger->warning('No content');
            return;
        }

        $copy = $this->jsonContent;
        $this->jsonContent = [];

        foreach ($copy as $date => $sunday) {
            if (is_string($date) !== true || is_string($sunday) !== true) {
                $this->logger->warning('Abbr/name incorrect');
                continue;
            }

            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date) !== 1) {
                $this->logger->warning('Date is not correct');
                continue;
            }

            $this->jsonContent[$date] = preg_replace('/[^A-Z0-9]/', '', $sunday);
        }

    }//end checkAndSanitize()


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
     * @param int $liturgicalYear Liturgical year (calendar year until the day before 1st Sunday of Advent, calendar year+1 afterwards)
     *
     * @return void
     *
     * @see https://www.omnicalculator.com/everyday-life/easter
     * @see https://www.omnicalculator.com/everyday-life/moon-phase
     * @see https://catholic-resources.org/Lectionary/
     */
    private function buildJson(int $liturgicalYear): void
    {
        $this->jsonContent = [];

        $odi = new \DateInterval('P1D');
        $owi = new \DateInterval('P1W');

        $start = new \DateTime(($liturgicalYear - 1).'-11-27');
        while (intval($start->format('w')) !== 0) {
            $start->add($odi);
        }

        $this->jsonContent[$start->format('d.m.Y')] = 'SOA1';
        $start->add($owi);
        $this->jsonContent[$start->format('d.m.Y')] = 'SOA2';
        $start->add($owi);
        $this->jsonContent[$start->format('d.m.Y')] = 'SOA3';
        $start->add($owi);
        $this->jsonContent[$start->format('d.m.Y')] = 'SOA4';
        $start->add($owi);
        if (intval($start->format('d')) === 25) {
            $this->jsonContent[$start->format('d.m.Y')] = 'SAC1B';
        } else {
            $this->jsonContent[$start->format('d.m.Y')] = 'SAC1';
        }

        $start->add($owi);
        if (intval($start->format('m')) === 1 && intval($start->format('d')) > 6) {
            $this->jsonContent[$start->format('d.m.Y')] = 'FOTBOTL';
        } else {
            $this->jsonContent[$start->format('d.m.Y')] = 'SAC2';
            $start->add($owi);
            $this->jsonContent[$start->format('d.m.Y')] = 'FOTBOTL';
        }

        // Now we need to know the date of Ash Wednesday.
        $equinox = new \DateTime($liturgicalYear.'-03-21');
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
        while (intval($ash->format('w')) !== 0) {
            // Find first sunday after that full moon.
            $ash = $ash->add($odi);
        }

        $ash->sub(new \DateInterval("P46D"));
        // Subtract 46 days (that is Ash Wednesday).
        $sund = 2;

        $start->add($owi);

        // Add Ordinary Time Sundays until before Ash Wednesday.
        while ($start->diff($ash)->format('%R') === '+') {
            $this->jsonContent[$start->format('d.m.Y')] = "SIOT${sund}";
            $start->add($owi);
            $sund++;
        }

        // Now we need to know the beginning of Advent.
        $ce = new \DateTime($liturgicalYear.'-11-27');
        while (intval($ce->format('w')) !== 0) {
            $ce->add($odi);
        }

        $ce->sub($odi);

        // To skip or not to skip? We need to finish with 34th Sunday in O.T.
        $dt3   = clone $start;
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
            $this->jsonContent[$start->format('d.m.Y')] = "SOL${i}";
            $start->add($owi);
        }

        // Add Palm Sunday.
        $this->jsonContent[$start->format('d.m.Y')] = "PaS";
        $start->add($owi);

        // Add Easter Sunday.
        $this->jsonContent[$start->format('d.m.Y')] = "ES";
        $start->add($owi);

        // Add 6 Sundays until before Pentecost Sunday.
        for ($i = 2; $i <= 7; $i++) {
            $this->jsonContent[$start->format('d.m.Y')] = "SOE${i}";
            $start->add($owi);
        }

        // Fill Sundays until before Advent.
        while ($start->diff($ce)->format('%R') === '+') {
            $this->jsonContent[$start->format('d.m.Y')] = "SIOT${sund}";
            $start->add($owi);
            $sund++;
        }

        $this->saveJson();

    }//end buildJson()


    /**
     * Hello
     *
     * @param int $time Timestamp
     *
     * @return string
     */
    public function dateToSunday(int $time): string
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        $date = date('d.m.Y', $time);
        if (array_key_exists($date, $this->jsonContent) !== true) {
            throw new ModelException('Date "'.$date.'" not found in lectionary', ModelException::CODE_PARAMETER);
        }

        return $this->jsonContent[$date];

    }//end dateToSunday()


    /**
     * Hello
     *
     * @param string $sunday Sunday abbreviation
     *
     * @return int
     */
    public function sundayToDate(string $sunday): int
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        $key = array_search($sunday, $this->jsonContent);
        if ($key === false || is_int($key) !== true) {
            throw new ModelException('Sunday "'.$sunday.'" not found in lectionary', ModelException::CODE_PARAMETER);
        }

        return $key;

    }//end sundayToDate()


}//end class
