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

use TMD\OrderOfMass\Exceptions\ModelException;

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
                $this->logger->warning('Code/data incorrect');
                continue;
            }

            if (preg_match('/^[a-z]{3}$/', $code) !== 1) {
                $this->logger->warning('Code is not correct');
                continue;
            }

            $this->jsonContent[$code] = [];
            foreach ($data as $key => $value) {
                if ($key === 'Part2B' && is_string($value) === true) {
                    $this->jsonContent[$code]['Part2B'] = preg_replace('/[^a-z]/', '', $value);
                    continue;
                }

                if ($key === 'Part2T' && is_string($value) === true) {
                    $this->jsonContent[$code]['Part2T'] = preg_replace('/[^a-z]/', '', $value);
                    continue;
                }

                if ($key === 'Part1' && is_string($value) === true) {
                    $this->jsonContent[$code]['Part1'] = preg_replace('/[^a-z]/', '', $value);
                    continue;
                }

                if ($key === 'Scope' && is_string($value) === true) {
                    $this->jsonContent[$code]['Scope'] = preg_replace('/[^IMS]/', '', $value);
                    continue;
                }

                if ($key === 'Language_Type' && is_string($value) === true) {
                    $this->jsonContent[$code]['Language_Type'] = preg_replace('/[^LECAHS]/', '', $value);
                    continue;
                }

                if ($key === 'Ref_Name' && is_string($value) === true) {
                    $this->jsonContent[$code]['Ref_Name'] = htmlspecialchars($value);
                    continue;
                }

                if ($key === 'Comment' && is_string($value) === true) {
                    $this->jsonContent[$code]['Comment'] = htmlspecialchars($value);
                    continue;
                }
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
            throw new ModelException('Language code "'.$language.'" not found', ModelException::CODE_PARAMETER);
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
    public function getLanguagePart2B(string $language): string
    {
        $data = $this->getLanguageData($language);
        if (array_key_exists('Part2B', $data) !== true) {
            throw new ModelException('Part2B not found in language data "'.$language.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['Part2B'];

    }//end getLanguagePart2B()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return string
     */
    public function getLanguagePart2T(string $language): string
    {
        $data = $this->getLanguageData($language);
        if (array_key_exists('Part2T', $data) !== true) {
            throw new ModelException('Part2T not found in language data "'.$language.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['Part2T'];

    }//end getLanguagePart2T()


    /**
     * Hello
     *
     * @param string $language Language code
     *
     * @return string
     */
    public function getLanguagePart1(string $language): string
    {
        $data = $this->getLanguageData($language);
        if (array_key_exists('Part1', $data) !== true) {
            throw new ModelException('Part1 not found in language data "'.$language.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['Part1'];

    }//end getLanguagePart1()


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
        if (array_key_exists('Ref_Name', $data) !== true) {
            throw new ModelException('Ref_Name not found in language data "'.$language.'"', ModelException::CODE_STRUCTURE);
        }

        return $data['Ref_Name'];

    }//end getLanguageName()


}//end class
