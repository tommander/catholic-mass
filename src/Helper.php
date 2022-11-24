<?php
/**
 * Helper unit
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

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
        $commitFileName = __DIR__.'/../commit';
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
     * Creates a chapter-verse-statement number (CVSN).
     *
     * `CvvvvSS` - chapter number followed by verse number with exactly four digits (zero-padded) and statement number with exactly two digis (zero-padded)).
     *
     * For Bible, this number can happily fit into a 32-bit signed integer.
     *
     * @param int $chap Chapter
     * @param int $ver  Verse
     * @param int $sta  Statement (none = 0, a = 1, b = 2, ...)
     *
     * @return int
     */
    public static function chapVer(int $chap, int $ver, int $sta=0): int
    {
        return (int) sprintf('%d%04d%02d', $chap, $ver, $sta);

    }//end chapVer()


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
     * Loads a JSON file into a PHP-friendly structure
     *
     * Basically a tiny little wrapper around {@see json_decode()}.
     *
     * @param string $fileName Path to the file incl. full file name
     * @param bool   $assoc    JSON objects will be converted to associative arrays instead of objects (default: `true`)
     *
     * @return mixed Content of the file or an empty array
     */
    public static function loadJson(string $fileName, bool $assoc=true)
    {
        if ($fileName === '') {
            return [];
        }

        $fileName2 = self::fullFilename($fileName);

        if (file_exists($fileName2) !== true) {
            return [];
        }

        $aFileCont = file_get_contents($fileName2);
        if ($aFileCont === false) {
            return [];
        }

        $a = json_decode($aFileCont, $assoc);
        if ($a === null) {
            return [];
        }

        return $a;

    }//end loadJson()


    /**
     * Returns the Sunday year cycle (A,B,C) for the given year
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
