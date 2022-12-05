<?php
/**
 * Zefania-bibles json Model Unit
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
 * Zefania-bibles json Model
 */
class BiblejsonModel extends BaseModel
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
     * @param string $file Bible file
     *
     * @return bool
     */
    public function load(string $file): bool
    {
        if ($file === '') {
            return false;
        }

        return $this->loadJson(sprintf('libs/zefania-bibles/json/%s', $file));

    }//end load()


    /**
     * Hello
     *
     * @return ?array
     */
    public function listVerses(): ?array
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        return array_keys($this->jsonContent);

    }//end listVerses()


    /**
     * Hello
     *
     * @param int $verse Verse reference
     *
     * @return string
     */
    public function getVerse(int $verse): string
    {
        if (is_array($this->jsonContent) !== true) {
            return '';
        }

        $verseKey = strval($verse);
        if (array_key_exists($verseKey, $this->jsonContent) !== true) {
            return '';
        }

        return $this->jsonContent[$verseKey];

    }//end getVerse()


    /**
     * Hello
     *
     * @param int $verseStart First included verse
     * @param int $verseEnd   Last included verse
     *
     * @return string
     */
    public function getVerseRange(int $verseStart, int $verseEnd): string
    {
        if (is_array($this->jsonContent) !== true) {
            return '';
        }

        $ret = '';
        foreach ($this->jsonContent as $ref => $text) {
            $refInt = intval($ref);
            if ($refInt < $verseStart || $refInt > $verseEnd) {
                continue;
            }

            if ($ret !== '') {
                $ret .= ' ';
            }

            $ret .= $text;
        }

        return $ret;

    }//end getVerseRange()


}//end class
