<?php
/**
 * Xxx-labels.json Model Unit
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
 * Xxx-labels.json Model
 */
class LanglabelsModel extends BaseModel
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
     * @param string $languageCode Language code
     *
     * @return bool
     */
    public function load(string $languageCode): bool
    {
        return $this->loadJson(sprintf('assets/json/lang/%s-labels.json', $languageCode));

    }//end load()


    /**
     * Hello
     *
     * @param string $label Label key
     *
     * @return string
     */
    public function getLabel(string $label): string
    {
        if (is_array($this->jsonContent) !== true) {
            return '';
        }

        if (array_key_exists('labels', $this->jsonContent) !== true) {
            return '';
        }

        if (is_array($this->jsonContent['labels']) !== true) {
            return '';
        }

        if (array_key_exists($label, $this->jsonContent['labels']) !== true) {
            return '';
        }

        return $this->jsonContent['labels'][$label];

    }//end getLabel()


    /**
     * Hello
     *
     * @param string $mystery Mystery key
     *
     * @return string
     */
    public function getMystery(string $mystery): string
    {
        if (is_array($this->jsonContent) !== true) {
            return '';
        }

        if (array_key_exists('mysteries', $this->jsonContent) !== true) {
            return '';
        }

        if (is_array($this->jsonContent['mysteries']) !== true) {
            return '';
        }

        if (array_key_exists($mystery, $this->jsonContent['mysteries']) !== true) {
            return '';
        }

        return $this->jsonContent['mysteries'][$mystery];

    }//end getMystery()


    /**
     * Hello
     *
     * @param string $sunday Sunday key
     *
     * @return string
     */
    public function getSunday(string $sunday): string
    {
        if (is_array($this->jsonContent) !== true) {
            return '';
        }

        if (array_key_exists('sundays', $this->jsonContent) !== true) {
            return '';
        }

        if (is_array($this->jsonContent['sundays']) !== true) {
            return '';
        }

        if (array_key_exists($sunday, $this->jsonContent['sundays']) !== true) {
            return '';
        }

        return $this->jsonContent['sundays'][$sunday];

    }//end getSunday()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return ?array
     */
    public function getBookData(string $abbreviation): ?array
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        if (array_key_exists('bible', $this->jsonContent) !== true) {
            return null;
        }

        if (is_array($this->jsonContent['bible']) !== true) {
            return null;
        }

        if (array_key_exists($abbreviation, $this->jsonContent['bible']) !== true) {
            return null;
        }

        return $this->jsonContent['bible'][$abbreviation];

    }//end getBookData()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return ?string
     */
    public function getBookAbbreviation(string $abbreviation): ?string
    {
        $data = $this->getBookData($abbreviation);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('abbr', $data) !== true) {
            return null;
        }

        return $data['abbr'];

    }//end getBookAbbreviation()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return ?string
     */
    public function getBookName(string $abbreviation): ?string
    {
        $data = $this->getBookData($abbreviation);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('title', $data) !== true) {
            return null;
        }

        return $data['title'];

    }//end getBookName()


}//end class
