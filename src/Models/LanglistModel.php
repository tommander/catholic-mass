<?php
/**
 * Langlist.json Model Unit
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
 * Langlist.json Model
 */
class LanglistModel extends BaseModel
{


    /**
     * Initialization called at the end of the constructor
     *
     * @return void
     */
    protected function initModel()
    {
        $this->loadJson('assets/json/langlist.json');

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
     * @param string $selected Selected language code
     *
     * @return array
     */
    public function listLanguagesForSelect(string $selected=''): array
    {
        $ret = [];

        if (is_array($this->jsonContent) !== true) {
            return $ret;
        }

        foreach ($this->jsonContent as $langKey => $langData) {
            if (is_array($langData) !== true) {
                continue;
            }

            $title = '';
            if (array_key_exists('title', $langData) === true) {
                $title = $langData['title'];
            }

            $ret[$langKey] = [
                'value' => $langKey,
                'sel'   => ($langKey === $selected),
                'text'  => $title,
            ];
        }//end foreach

        return $ret;

    }//end listLanguagesForSelect()


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
    public function getLanguageName(string $language): ?string
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('title', $data) !== true) {
            return null;
        }

        return $data['title'];

    }//end getLanguageName()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?string
     */
    public function getLanguageAuthor(string $language): ?string
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('author', $data) !== true) {
            return null;
        }

        return $data['author'];

    }//end getLanguageAuthor()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return ?array
     */
    public function getLanguageLinks(string $language): ?array
    {
        $data = $this->getLanguageData($language);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('link', $data) !== true) {
            return null;
        }

        return $data['link'];

    }//end getLanguageLinks()


}//end class
