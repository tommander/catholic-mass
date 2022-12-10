<?php
/**
 * Helper unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

use TMD\OrderOfMass\Exceptions\{OomException,ModelException};

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Collection of static helper functions grouped together in the Helper class.
 *
 * There is no need to create instances of this class, so it does not need to be in a container, too.
 * Just make sure it is accessible via autoload.
 */
class Helper
{


    /**
     * Hash with `ripemd160`
     *
     * @param string $input Input text to hash
     *
     * @return string
     */
    public static function hash(string $input): string
    {
        if (in_array('adler32', \hash_algos()) !== true) {
            return '';
        }

        return hash('adler32', $input);

    }//end hash()


    /**
     * This function returns a link to a particular commit that was deployed.

     * The function is quite strict, so if the file does not exist or does not contain
     * precisely 40 hexadecimal characters (lowercase), it returns an empty string.
     *
     * Note that the file "commit" is not in the repository; it makes sense only in a
     * particular deployment site.
     *
     * @return string HTML link ("a" tag) to a particular deployed commit.
     *
     * @todo We can actually find the commit number in the .git folder, but the question is - do we have/need .git at the deployment site?
     */
    public static function showCommit()
    {
        $commitFileName = self::fullFilename('.git/refs/heads/main');
        if (file_exists($commitFileName) === false) {
            return '';
        }

        $commit = trim(file_get_contents($commitFileName));
        if (preg_match('/^[a-f0-9]{40}$/', $commit) !== 1) {
            return '';
        }

        return sprintf(' (<a href="https://github.com/tommander/catholic-mass/commit/%s">commit %s</a>)', $commit, substr($commit, 0, 7));

    }//end showCommit()


    /**
     * Convert latin letters a-z to numbers 1-26. Case insensitive.
     *
     * @param string $chr Letter A-Z or a-z
     *
     * @return int
     */
    public static function letterToInt(string $chr): int
    {
        if (strlen($chr) < 1) {
            return 0;
        }

        $letter = strtoupper($chr[0]);
        return (ord($letter) - 64);

    }//end letterToInt()


    /**
     * Returns full file path and name
     *
     * This function should be used in the PHP files in the src/ folder.
     *
     * @param string $fileName File name with path relative to root
     *
     * @return string
     */
    public static function fullFilename(string $fileName): string
    {
        return __DIR__.'/../'.$fileName;

    }//end fullFilename()


    /**
     * Returns the Sunday year cycle (A,B,C) for the given year
     *
     * Beware that the liturgical year starts with the first Sunday of Advent, so you have to increase the year number in case you are already in the first week of the liturgical year
     *
     * @param int $year Year
     *
     * @return string
     */
    public static function sundayCycle($year)
    {
        $ret = '';
        $mod = ($year % 3);
        switch ($mod) {
        case 0:
            $ret = 'C';
            break;
        case 1:
            $ret = 'A';
            break;
        case 2:
            $ret = 'B';
            break;
        }

        return $ret;

    }//end sundayCycle()


    /**
     * Returns the weekday year cycle (I,II) for the given year
     *
     * Beware that the liturgical year starts with the first Sunday of Advent, so you have to increase the year number in case you are already in the first week of the liturgical year
     *
     * @param mixed $year Year
     *
     * @return string
     */
    public static function weekdayCycle($year)
    {
        if (($year % 2) === 0) {
            return 'II';
        }

        return "I";

    }//end weekdayCycle()


    /**
     * Returns the timestamp of the next Sunday after the given day (or that day, if it's Sunday)
     *
     * @param int $time Unix timestamp
     *
     * @return int
     */
    public static function nextSunday($time)
    {
        while (date('w', $time) !== '0') {
            $time += 86400;
        }

        return $time;

    }//end nextSunday()


    /**
     * Retrieve liturgical year
     *
     * For days prior to 1st Sunday of Advent, it returns the calendar year.
     * For 1st Sunday of Advent and later, it returns the next calendar year.
     *
     * @param int $time Timestamp
     *
     * @return int
     */
    public static function getLiturgicalYear(int $time): int
    {
        $date = new \DateTime('@'.$time);
        $year = intval($date->format('Y'));
        $odi  = new \DateInterval('P1D');
        $fas  = new \DateTime($year.'-11-27');
        while ($fas->format('w') !== '0') {
            $fas->add($odi);
        }

        if ($fas->diff($date)->format('%R') === '+') {
            return ($year + 1);
        }

        return $year;

    }//end getLiturgicalYear()


    /**
     * Returns the Holy Rosary mysteries for the given day.
     *
     * This is the version with Luminous mysteries.
     *
     * | Week day | Mystery   |
     * | -------- | --------- |
     * | Su       | Glorious  |
     * | Mo       | Joyful    |
     * | Tu       | Sorrowful |
     * | We       | Glorious  |
     * | Th       | Luminous  |
     * | Fr       | Sorrowful |
     * | Sa       | Joyful    |
     *
     * @param int $time Unix timestamp
     *
     * @return string g/s/j/l
     *
     * @todo Do we want to be able to also return mysteries pre-luminous way?
     */
    public static function todaysMystery($time)
    {
        switch (date('w', $time)) {
        case 0:
            return 'g';
        case 1:
            return 'j';
        case 2:
            return 's';
        case 3:
            return 'g';
        case 4:
            return 'l';
        case 5:
            return 's';
        case 6:
            return 'j';
        }

        return '';

    }//end todaysMystery()


}//end class
