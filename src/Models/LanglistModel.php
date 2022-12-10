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

use TMD\OrderOfMass\Exceptions\ModelException;

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

        foreach ($copy as $code => $data) {
            if (is_string($code) !== true || is_array($data) !== true) {
                $this->logger->warning('Incorrect code/data');
                continue;
            }

            $codeClean = preg_replace('[^a-z]', '', $code);
            if ($codeClean === '') {
                $this->logger->warning('Language code empty after cleaning');
                continue;
            }

            $this->jsonContent[$codeClean] = [];
            foreach ($data as $key => $value) {
                if ($key === 'title' && is_string($value) === true) {
                    $this->jsonContent[$codeClean]['title'] = htmlspecialchars($value);
                    continue;
                }

                if ($key === 'author' && is_string($value) === true) {
                    $this->jsonContent[$codeClean]['author'] = htmlspecialchars($value);
                    continue;
                }

                if ($key === 'link' && is_array($value) === true) {
                    $this->jsonContent[$codeClean]['link'] = [];
                    foreach ($value as $link) {
                        $this->jsonContent[$codeClean]['link'][] = htmlspecialchars($link);
                    }

                    continue;
                }

                $this->logger->warning('Unknown language data key/value');
            }//end foreach
        }//end foreach

    }//end checkAndSanitize()


    /**
     * Hello
     *
     * @return array
     */
    public function listLanguages(): array
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
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
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        $ret = [];
        foreach ($this->jsonContent as $langKey => $langData) {
            if (is_array($langData) !== true) {
                throw new ModelException('Language data for "'.$langKey.'" is not array', ModelException::CODE_STRUCTURE);
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
     * @return array
     */
    public function getLanguageData(string $language): array
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($language, $this->jsonContent) !== true) {
            throw new ModelException('Language "'.$language.'" not found', ModelException::CODE_PARAMETER);
        }

        return $this->jsonContent[$language];

    }//end getLanguageData()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return string
     */
    public function getLanguageName(string $language): string
    {
        $data = $this->getLanguageData($language);
        if (array_key_exists('title', $data) !== true) {
            throw new ModelException('Language data for "'.$language.'" does not contain "title"', ModelException::CODE_STRUCTURE);
        }

        return $data['title'];

    }//end getLanguageName()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return string
     */
    public function getLanguageAuthor(string $language): string
    {
        $data = $this->getLanguageData($language);
        if (array_key_exists('author', $data) !== true) {
            throw new ModelException('Language data for "'.$language.'" does not contain "author"', ModelException::CODE_STRUCTURE);
        }

        return $data['author'];

    }//end getLanguageAuthor()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return array
     */
    public function getLanguageLinks(string $language): array
    {
        $data = $this->getLanguageData($language);
        if (array_key_exists('link', $data) !== true) {
            throw new ModelException('Language data for "'.$language.'" does not contain "link"', ModelException::CODE_STRUCTURE);
        }

        return $data['link'];

    }//end getLanguageLinks()


}//end class
