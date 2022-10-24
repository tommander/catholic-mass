<?php
/**
 * Helper functions for the Order of Mass app
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
 * Class for helper function of the Order of Mass app
 */
class Helper
{


    /**
     * Do. Not. Call. Me.
     */
    public function __construct()
    {
        die('Please do not create instances of '.self::class);

    }//end __construct()


    /**
     * This function returns a link to a particular commit that was deployed.

     * The function is quite strict, so if the file does not exist or does not contain
     * precisely 40 hexadecimal characters (lowercase), it returns an empty string.
     *
     * Note that the file "commit" is not in the repository; it makes sense only in a
     * particular deployment site.
     *
     * @return string HTML link ("a" tag) to a particular deployed commit.
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
     * Parse single string reference to elementary parts
     *
     * Return associative array, where key is the original reference (parameter $ref)
     * and value is an array of elementarized arrays.
     *
     * Elementarized array has three keys - book (string), chap (string) and ver, which
     * is either string (single verse) or array (verse range from-to)
     *
     * @param string $ref One reference
     *
     * @return array
     *
     * @todo Need to be able to parse verse statements
     */
    public static function parseRef(string $ref): array
    {
        // Check that book reference and the rest of the reference was found.
        if (preg_match('/^([\p{L}0-9]+)\s+(.*)$/u', $ref, $mat) !== 1 || count($mat) < 3) {
            return [];
        }

        // Save basic reference parts for clarity.
        $tmp  = [
            $mat[1],
            0,
            0,
            '',
        ];
        $chap = '';

        // Split reference into single verses or verse ranges.
        $rngArr = [];
        $rngTok = strtok($mat[2], ',+');
        while ($rngTok !== false) {
            $rngArr[] = $rngTok;
            $rngTok   = strtok(',+');
        }

        // Unification of all single verse/verse range refs, so that
        // they all have book and chapter.
        $rng = [];
        foreach ($rngArr as $rngOne) {
            if (preg_match('/(([0-9]+):)?(\d+)(-)?(([0-9]+):)?(\d+)?/', $rngOne, $mat2) === 1) {
                if (count($mat2) === 4) {
                    if ($mat2[2] !== '') {
                        $chap = $mat2[2];
                    }

                    $tmp[1] = self::chapVer(intval($chap), intval($mat2[3]));
                    $tmp[2] = $tmp[1];
                } else if (count($mat2) === 8) {
                    if ($mat2[2] !== '') {
                        $chap = $mat2[2];
                    }

                    $tmp[1] = self::chapVer(intval($chap), intval($mat2[3]));
                    if ($mat2[6] !== '') {
                        $chap = $mat2[6];
                    }

                    $tmp[2] = self::chapVer(intval($chap), intval($mat2[7]));
                }

                $rng[] = [
                    $mat[1],
                    $tmp[1],
                    $tmp[2],
                ];
            }//end if
        }//end foreach

        return $rng;

    }//end parseRef()


    /**
     * Function that allows for parsing either string reference or array of string
     * references
     *
     * @param mixed $refs Reference(s)
     *
     * @return array
     *
     * @see parseRef()
     */
    public static function parseRefs($refs): array
    {
        if (is_string($refs) === true) {
            return Helper::parseRef($refs);
        }

        if (is_array($refs) === true) {
            $arr = [];
            foreach ($refs as $oneref) {
                $arr = array_merge($arr, Helper::parseRef($oneref));
            }

            return $arr;
        }

        return [];

    }//end parseRefs()


    /**
     * Creates a chapter-verse number (chapter number followed by verse number with exactly four digits (zero-padded))
     *
     * @param int $chap Chapter
     * @param int $ver  Verse
     *
     * @return int
     */
    public static function chapVer(int $chap, int $ver): int
    {
        return (int) sprintf('%d%04d', $chap, $ver);

    }//end chapVer()


    /**
     * Returns full file path and name
     *
     * @param string $fileName Filename (with path relative to root)
     *
     * @return string
     */
    public static function fullFilename(string $fileName): string
    {
        return __DIR__.'/../'.$fileName;

    }//end fullFilename()


    /**
     * Loads a JSON lectionary file into an associative array
     *
     * @param string $fileName Path to the file incl. full file name
     * @param bool   $assoc    JSON objects will be converted to associative arraysinstead of objects (default: `true`)
     *
     * @return mixed Content of the file or an empty array
     */
    public static function loadJson(string $fileName, bool $assoc=true)
    {
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
     * Returns the timestamp of the next Sunday (or today, if it's Sunday)
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
     * Returns today's kind of the Holy Rosary mystery
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
