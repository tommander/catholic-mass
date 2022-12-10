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

use TMD\OrderOfMass\Exceptions\ModelException;

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
        $this->checkAndSanitize();

    }//end initModel()


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

        foreach ($copy as $abbr => $name) {
            if (is_string($abbr) !== true || is_string($name) !== true) {
                $this->logger->warning('Abbr/name incorrect');
                continue;
            }

            $abbrClean = preg_replace('/[^A-z0-9]/', '', $abbr);
            if ($abbrClean === '') {
                $this->logger->warning('BookNum empty after cleaning');
                continue;
            }

            $this->jsonContent[$abbrClean] = preg_replace('/[^A-z #0-9()]/', '', $name);
        }

    }//end checkAndSanitize()


    /**
     * Hello
     *
     * @return array
     */
    public function listAbbreviations(): array
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
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
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        return array_values($this->jsonContent);

    }//end listNames()


    /**
     * Hello
     *
     * @param int $bookNum Book number
     *
     * @return string
     */
    public function numberToAbbreviation(int $bookNum): string
    {
        if ($bookNum <= 0) {
            throw new ModelException('Book number "'.$bookNum.'" has to be greater than zero', ModelException::CODE_PARAMETER);
        }

        $bookIndex = ($bookNum - 1);

        $abbreviations = $this->listAbbreviations();
        if ($bookIndex >= count($abbreviations)) {
            throw new ModelException('Book number "'.$bookNum.'" is greater than the number of common books "'.count($abbreviations).'"', ModelException::CODE_PARAMETER);
        }

        return $abbreviations[$bookIndex];

    }//end numberToAbbreviation()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return int
     */
    public function abbreviationToNumber(string $abbreviation): int
    {
        $abbreviations = $this->listAbbreviations();

        $res = array_search($abbreviation, $abbreviations);
        if ($res === false || is_int($res) !== true) {
            throw new ModelException('Book abbreviation "'.$abbreviation.'" is unknown', ModelException::CODE_PARAMETER);
        }

        return ($res + 1);

    }//end abbreviationToNumber()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return string
     */
    public function abbreviationToName(string $abbreviation): string
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($abbreviation, $this->jsonContent) !== true) {
            throw new ModelException('Book abbreviation "'.$abbreviation.'" not found', ModelException::CODE_PARAMETER);
        }

        return $this->jsonContent[$abbreviation];

    }//end abbreviationToName()


    /**
     * Hello
     *
     * @param string $name Book name
     *
     * @return string
     */
    public function nameToAbbreviation(string $name): string
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        $res = array_search($name, $this->jsonContent);
        if ($res === false) {
            throw new ModelException('Book name "'.$name.'" not found', ModelException::CODE_PARAMETER);
        }

        return $res;

    }//end nameToAbbreviation()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return string
     */
    public function numberToName(int $num): string
    {
        return $this->abbreviationToName($this->numberToAbbreviation($num));

    }//end numberToName()


    /**
     * Hello
     *
     * @param string $name Book name
     *
     * @return int
     */
    public function nameToNumber(string $name): int
    {
        return $this->abbreviationToNumber($this->nameToAbbreviation($name));

    }//end nameToNumber()


}//end class
