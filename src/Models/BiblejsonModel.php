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

use TMD\OrderOfMass\Exceptions\ModelException;

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
     * @return void
     */
    public function load(string $file): void
    {
        // We let this function end quietly in case of empty filename. This covers
        // the case that PARAM_BIBLE is empty or has an unknown value, which is e.g.
        // when a user visits the website for the first time.
        if ($file === '') {
            return;
        }

        $this->loadJson(sprintf('libs/zefania-bibles/json/%s', $file));
        $this->checkAndSanitize();

    }//end load()


    /**
     * This function is just checking whether the JSON is loaded as an array.
     *
     * We cannot do any sanitizing here, because it takes a long long time, so
     * it's better to do it before echoing the content.
     *
     * So we just check the structure of the JSON and remove what does not belong
     * there.
     *
     * @return void
     */
    private function checkAndSanitize(): void
    {
        if (is_array($this->jsonContent) !== true) {
            $this->logger->warning('No content');
            return;
        }

        $unset = [];
        foreach ($this->jsonContent as $verse => $text) {
            if (is_int($verse) !== true || is_string($text) !== true) {
                $unset[] = $verse;
            }
        }

        foreach ($unset as $unsetKey) {
            unset($this->jsonContent[$unsetKey]);
        }

    }//end checkAndSanitize()


    /**
     * Hello
     *
     * @return array
     */
    public function listVerses(): array
    {
        if (is_array($this->jsonContent) !== true) {
            return [];
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
