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
     * @param int  $liturgicalYear       Liturgical year (calendar year until the day before 1st Sunday of Advent, calendar year+1 afterwards)
     * @param bool $traditionalEpiphany  Whether the Epiphany of the Lord is strictly on Jan 6 (true) or on the 1st Sunday after New Year's Day (false)
     * @param bool $traditionalAscension Whether the Ascension of the Lord is strictly on 40th day of Easter (true) or on the 7th Sunday of Easter (false)
     *
     * @return void
     *
     * @see https://www.omnicalculator.com/everyday-life/easter
     * @see https://www.omnicalculator.com/everyday-life/moon-phase
     * @see https://catholic-resources.org/Lectionary/
     */
    private function buildJson(int $liturgicalYear, bool $traditionalEpiphany=true, bool $traditionalAscension=false): void
    {
        $this->jsonContent = [];

        $odi = new \DateInterval('P1D');

        $shortDays = [
            '0' => 'Su',
            '1' => 'Mo',
            '2' => 'Tu',
            '3' => 'We',
            '4' => 'Th',
            '5' => 'Fr',
            '6' => 'Sa',
        ];

        // phpcs:disable Squiz.Arrays.ArrayDeclaration.SingleLineNotAllowed
        $daysUntilBaptism = [
            '27' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'FroA3', 'Dec17', 'SuoA4', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'FroA3', 'Dec17', 'SuoA4', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'Jan6', 'Jan7', 'EoTL', 'BotL'],
            ],
            '28' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'Dec17', 'Dec18', 'SuoA4', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'FotHF', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'SuaC2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'Dec17', 'Dec18', 'SuoA4', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'FotHF', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'EotL', 'Jan3', 'Jan4', 'Jan5', 'Jan6', 'Jan7', 'Jan8', 'BotL'],
            ],
            '29' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'Dec17', 'Dec18', 'Dec19', 'SuoA4', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'FotHF', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'SuaC2', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'Dec17', 'Dec18', 'Dec19', 'SuoA4', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'FotHF', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'EotL', 'Jan4', 'Jan5', 'Jan6', 'Jan7', 'Jan8', 'Jan9', 'BotL'],
            ],
            '30' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'SuoA4', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'FotHF', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'SuaC2', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'SuoA4', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'FotHF', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'EotL', 'Jan5', 'Jan6', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'BotL'],
            ],
            '01' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'SuoA4', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'FotHF', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'SuaC2', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'SuoA4', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'FotHF', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'EotL', 'Jan6', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'BotL'],
            ],
            '02' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'SuoA4', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'Jan12', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'SuoA4', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'Jan12', 'BotL'],
            ],
            '03' => [
                true  => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'FotHF', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'BotL'],
                false => ['SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'FotHF', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'Jan6', 'EotL', 'BotL'],
            ],
        ];

        // phpcs:enable

        // First we find the Sunday within 27-11 to 03-12, that is
        // the First Sunday of Advent, i.e. beginning of the
        // liturgical year.
        $start = new \DateTime(($liturgicalYear - 1).'-11-27');
        while (intval($start->format('w')) !== 0) {
            $start->add($odi);
        }

        // Now we add the days until the Baptism of the Lord.
        foreach ($daysUntilBaptism[$start->format('d')][$traditionalEpiphany] as $dayAbbr) {
            $this->jsonContent[$start->format('d.m.Y')] = $dayAbbr;
            $start->add($odi);
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
        $weekInOT = 1;

        // Add Ordinary Time Sundays until before Ash Wednesday.
        while ($start->diff($ash)->format('%R') === '+') {
            if ($start->format('w') === '0') {
                $weekInOT++;
            }

            $this->jsonContent[$start->format('d.m.Y')] = $shortDays[$start->format('w')]."iOT${weekInOT}";
            $start->add($odi);
        }

        // phpcs:disable Squiz.Arrays.ArrayDeclaration.SingleLineNotAllowed
        $daysUntilPentecost = [
            true  => ['WeAsh', 'ThaAW', 'FraAW', 'SaaAW', 'SuoL1', 'MooL1', 'TuoL1', 'WeoL1', 'ThoL1', 'FroL1', 'SaoL1', 'SuoL2', 'MooL2', 'TuoL2', 'WeoL2', 'ThoL2', 'FroL2', 'SaoL2', 'SuoL3', 'MooL3', 'TuoL3', 'WeoL3', 'ThoL3', 'FroL3', 'SaoL3', 'SuoL4', 'MooL4', 'TuoL4', 'WeoL4', 'ThoL4', 'FroL4', 'SaoL4', 'SuoL5', 'MooL5', 'TuoL5', 'WeoL5', 'ThoL5', 'FroL5', 'SaoL5', 'SuPalm', 'MooHW', 'TuoHW', 'WeoHW', 'ThHoly', 'FrGood', 'SaHoly', 'SuEaster', 'MooE1', 'TuoE1', 'WeoE1', 'ThoE1', 'FroE1', 'SaoE1', 'SuoE2', 'MooE2', 'TuoE2', 'WeoE2', 'ThoE2', 'FroE2', 'SaoE2', 'SuoE3', 'MooE3', 'TuoE3', 'WeoE3', 'ThoE3', 'FroE3', 'SaoE3', 'SuoE4', 'MooE4', 'TuoE4', 'WeoE4', 'ThoE4', 'FroE4', 'SaoE4', 'SuoE5', 'MooE5', 'TuoE5', 'WeoE5', 'ThoE5', 'FroE5', 'SaoE5', 'SuoE6', 'MooE6', 'TuoE6', 'WeoE6', 'AotL', 'FroE6', 'SaoE6', 'SuoE7', 'MooE7', 'TuoE7', 'WeoE7', 'ThoE7', 'FroE7', 'SaoE7', 'Pentecost'],
            false => ['WeAsh', 'ThaAW', 'FraAW', 'SaaAW', 'SuoL1', 'MooL1', 'TuoL1', 'WeoL1', 'ThoL1', 'FroL1', 'SaoL1', 'SuoL2', 'MooL2', 'TuoL2', 'WeoL2', 'ThoL2', 'FroL2', 'SaoL2', 'SuoL3', 'MooL3', 'TuoL3', 'WeoL3', 'ThoL3', 'FroL3', 'SaoL3', 'SuoL4', 'MooL4', 'TuoL4', 'WeoL4', 'ThoL4', 'FroL4', 'SaoL4', 'SuoL5', 'MooL5', 'TuoL5', 'WeoL5', 'ThoL5', 'FroL5', 'SaoL5', 'SuPalm', 'MooHW', 'TuoHW', 'WeoHW', 'ThHoly', 'FrGood', 'SaHoly', 'SuEaster', 'MooE1', 'TuoE1', 'WeoE1', 'ThoE1', 'FroE1', 'SaoE1', 'SuoE2', 'MooE2', 'TuoE2', 'WeoE2', 'ThoE2', 'FroE2', 'SaoE2', 'SuoE3', 'MooE3', 'TuoE3', 'WeoE3', 'ThoE3', 'FroE3', 'SaoE3', 'SuoE4', 'MooE4', 'TuoE4', 'WeoE4', 'ThoE4', 'FroE4', 'SaoE4', 'SuoE5', 'MooE5', 'TuoE5', 'WeoE5', 'ThoE5', 'FroE5', 'SaoE5', 'SuoE6', 'MooE6', 'TuoE6', 'WeoE6', 'ThoE6', 'FroE6', 'SaoE6', 'AotL', 'MooE7', 'TuoE7', 'WeoE7', 'ThoE7', 'FroE7', 'SaoE7', 'Pentecost'],
        ];
        // phpcs:enable

        // Now we add the days until the Pentecost Sunday.
        foreach ($daysUntilPentecost[$traditionalAscension] as $dayAbbr) {
            $this->jsonContent[$start->format('d.m.Y')] = $dayAbbr;
            $start->add($odi);
        }

        // Now we need to know the beginning of Advent.
        $ce = new \DateTime($liturgicalYear.'-11-27');
        while (intval($ce->format('w')) !== 0) {
            $ce->add($odi);
        }

        // Go to the last day of our liturgical year and reset Week in Ordinary Time to 35.
        // The current day is Saturday and our loop decreases WiOT by 1 every Saturday,
        // so we will be starting with 34th week.
        $ce->sub($odi);
        $weekInOT = 35;

        $daysTmp = [];
        // Now we go back in time.
        while ($start->diff($ce)->format('%R') === '+') {
            if ($ce->format('w') === '6') {
                $weekInOT--;
            }

            if ($start->diff($ce)->format('%a') === '6') {
                $daysTmp[$ce->format('d.m.Y')] = 'SotMHT';
            } else if ($start->diff($ce)->format('%a') === '13') {
                $daysTmp[$ce->format('d.m.Y')] = 'SotMHBaBoC';
            } else {
                $daysTmp[$ce->format('d.m.Y')] = $shortDays[$ce->format('w')].'iOT'.$weekInOT;
            }

            $ce->sub($odi);
        }

        $this->jsonContent = \array_merge($this->jsonContent, \array_reverse($daysTmp, true));
        $this->saveJson(true);

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
