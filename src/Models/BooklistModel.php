<?php
/**
 * Booklist.json Model Unit
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
 * Booklist.json Model
 */
class BooklistModel extends BaseModel
{


    /**
     * Initialization called at the end of the constructor
     *
     * @return void
     */
    protected function initModel()
    {
        $this->loadJson('assets/json/booklist.json');

    }//end initModel()


    /**
     * Hello
     *
     * @return array
     */
    public function listAbbreviations(): array
    {
        if (is_array($this->jsonContent) !== true) {
            return [];
        }

        return array_keys($this->jsonContent);

    }//end listAbbreviations()


    /**
     * Hello
     *
     * @return array
     */
    public function listNames(): array
    {
        if (is_array($this->jsonContent) !== true) {
            return [];
        }

        return array_values($this->jsonContent);

    }//end listNames()


    /**
     * Hello
     *
     * @param int $bookNum Book number
     *
     * @return ?string
     */
    public function numberToAbbreviation(int $bookNum): ?string
    {
        if ($bookNum <= 0) {
            return null;
        }

        $bookIndex = ($bookNum - 1);

        $abbreviations = $this->listAbbreviations();
        if ($bookIndex >= count($abbreviations)) {
            return null;
        }

        return $abbreviations[$bookIndex];

    }//end numberToAbbreviation()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return ?int
     */
    public function abbreviationToNumber(string $abbreviation): ?int
    {
        $abbreviations = $this->listAbbreviations();

        $res = array_search($abbreviation, $abbreviations);
        if ($res === false || is_int($res) !== true) {
            return null;
        }

        return ($res + 1);

    }//end abbreviationToNumber()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return ?string
     */
    public function abbreviationToName(string $abbreviation): ?string
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        if (array_key_exists($abbreviation, $this->jsonContent) !== true) {
            return null;
        }

        return $this->jsonContent[$abbreviation];

    }//end abbreviationToName()


    /**
     * Hello
     *
     * @param string $name Book name
     *
     * @return ?string
     */
    public function nameToAbbreviation(string $name): ?string
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        $res = array_search($name, $this->jsonContent);
        if ($res === false) {
            return null;
        }

        return $res;

    }//end nameToAbbreviation()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return ?string
     */
    public function numberToName(int $num): ?string
    {
        $abbreviation = $this->numberToAbbreviation($num);
        if ($abbreviation === null) {
            return null;
        }

        return $this->abbreviationToName($abbreviation);

    }//end numberToName()


    /**
     * Hello
     *
     * @param string $name Book name
     *
     * @return ?int
     */
    public function nameToNumber(string $name): ?int
    {
        $abbreviation = $this->nameToAbbreviation($name);
        if ($abbreviation === null) {
            return null;
        }

        return $this->abbreviationToNumber($abbreviation);

    }//end nameToNumber()


}//end class
