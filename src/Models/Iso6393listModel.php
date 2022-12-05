<?php
/**
 * Iso6393list.json Model Unit
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
 * Iso6393list.json Model
 */
class Iso6393listModel extends BaseModel
{


    /**
     * Initialization called at the end of the constructor
     *
     * @return void
     */
    protected function initModel()
    {
        $this->loadJson('assets/json/iso6393list.json');

    }//end initModel()


    /**
     * Hello
     *
     * @return array
     */
    public function listLanguages(): array
    {
        if (is_array($this->jsonContent) !== true) {
            return [];
        }

        return array_keys($this->jsonContent);

    }//end listLanguages()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?array
     */
    public function getLanguageData(string $language): ?array
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        if (array_key_exists($language, $this->jsonContent) !== true) {
            return null;
        }

        return $this->jsonContent[$language];

    }//end getLanguageData()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?string
     */
    public function getLanguagePart2B(string $language): ?string
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('Part2B', $data) !== true) {
            return null;
        }

        return $data['Part2B'];

    }//end getLanguagePart2B()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?string
     */
    public function getLanguagePart2T(string $language): ?string
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('Part2T', $data) !== true) {
            return null;
        }

        return $data['Part2T'];

    }//end getLanguagePart2T()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?string
     */
    public function getLanguagePart1(string $language): ?string
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('Part1', $data) !== true) {
            return null;
        }

        return $data['Part1'];

    }//end getLanguagePart1()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?string
     */
    public function getLanguageName(string $language): ?string
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('Ref_Name', $data) !== true) {
            return null;
        }

        return $data['Ref_Name'];

    }//end getLanguageName()


}//end class
