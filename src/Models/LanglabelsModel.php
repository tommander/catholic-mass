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

use TMD\OrderOfMass\Exceptions\ModelException;

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
     * @return void
     */
    public function load(string $languageCode): void
    {
        $this->loadJson(sprintf('assets/json/lang/%s-labels.json', $languageCode));
        $this->checkAndSanitize();

    }//end load()


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

        foreach ($copy as $kind => $data) {
            if (in_array($kind, ['labels', 'mysteries', 'sundays', 'bible'], true) !== true) {
                $this->logger->warning('Incorrect labels kind "'.$kind.'"');
                continue;
            }

            if (is_array($data) !== true) {
                $this->logger->warning('Data for labels kind "'.$kind.'" is not array');
                continue;
            }

            $this->jsonContent[$kind] = [];
            if ($kind === 'bible') {
                $this->jsonContent[$kind] = [];
                foreach ($data as $commonAbbr => $bibleData) {
                    if (is_string($commonAbbr) !== true || is_array($bibleData) !== true) {
                        $this->logger->warning('Bible labels data item is incorrect');
                        continue;
                    }

                    $commonAbbrClean = preg_replace('/[^A-z0-9]/', '', $commonAbbr);
                    if ($commonAbbrClean === '') {
                        $this->logger->warning('Key in bible labels data empty after cleaning');
                        continue;
                    }

                    $this->jsonContent[$kind][$commonAbbrClean] = [];
                    foreach ($bibleData as $subKey => $subValue) {
                        if ($subKey === 'abbr' && is_string($subValue) === true) {
                            $this->jsonContent[$kind][$commonAbbrClean]['abbr'] = htmlspecialchars($subValue);
                            continue;
                        }

                        if ($subKey === 'title' && is_string($subValue) === true) {
                            $this->jsonContent[$kind][$commonAbbrClean]['title'] = htmlspecialchars($subValue);
                            continue;
                        }

                        $this->logger->warning('Unknown key/value pair in bible labels data');
                    }
                }//end foreach

                continue;
            }//end if

            foreach ($data as $key => $value) {
                if (is_string($key) !== true || is_string($value) !== true) {
                    $this->logger->warning('Incorrect key/value pair in labels data');
                    continue;
                }

                $keyClean = preg_replace('/[^A-z0-9]/', '', $key);
                if ($keyClean === '') {
                    $this->logger->warning('Key in labels data empty after cleaning');
                    continue;
                }

                $this->jsonContent[$kind][$keyClean] = htmlspecialchars($value);
            }
        }//end foreach

    }//end checkAndSanitize()


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
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists('labels', $this->jsonContent) !== true) {
            throw new ModelException('JSON content does not contain "label"', ModelException::CODE_STRUCTURE);
        }

        if (is_array($this->jsonContent['labels']) !== true) {
            throw new ModelException('JSON content subitem "label" is not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($label, $this->jsonContent['labels']) !== true) {
            throw new ModelException('Label "'.$label.'" not found', ModelException::CODE_PARAMETER);
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
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists('mysteries', $this->jsonContent) !== true) {
            throw new ModelException('JSON content does not contain "mysteries"', ModelException::CODE_STRUCTURE);
        }

        if (is_array($this->jsonContent['mysteries']) !== true) {
            throw new ModelException('JSON content subitem "mysteries" is not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($mystery, $this->jsonContent['mysteries']) !== true) {
            throw new ModelException('Mystery "'.$mystery.'" not found', ModelException::CODE_PARAMETER);
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
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists('sundays', $this->jsonContent) !== true) {
            throw new ModelException('JSON content does not contain "sundays"', ModelException::CODE_STRUCTURE);
        }

        if (is_array($this->jsonContent['sundays']) !== true) {
            throw new ModelException('JSON content subitem "sundays" is not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($sunday, $this->jsonContent['sundays']) !== true) {
            throw new ModelException('Sunday "'.$sunday.'" not found', ModelException::CODE_PARAMETER);
        }

        return $this->jsonContent['sundays'][$sunday];

    }//end getSunday()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return array
     */
    public function getBookData(string $abbreviation): array
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists('bible', $this->jsonContent) !== true) {
            throw new ModelException('JSON content does not contain "bible"', ModelException::CODE_STRUCTURE);
        }

        if (is_array($this->jsonContent['bible']) !== true) {
            throw new ModelException('JSON content subitem "bible" is not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($abbreviation, $this->jsonContent['bible']) !== true) {
            throw new ModelException('Common book abbreviation "'.$abbreviation.'" not found', ModelException::CODE_PARAMETER);
        }

        return $this->jsonContent['bible'][$abbreviation];

    }//end getBookData()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return string
     */
    public function getBookAbbreviation(string $abbreviation): string
    {
        $data = $this->getBookData($abbreviation);
        if (array_key_exists('abbr', $data) !== true) {
            throw new ModelException('Book data for "'.$abbreviation.'" does not contain "abbr"', ModelException::CODE_STRUCTURE);
        }

        return $data['abbr'];

    }//end getBookAbbreviation()


    /**
     * Hello
     *
     * @param string $abbreviation Book abbreviation
     *
     * @return string
     */
    public function getBookName(string $abbreviation): string
    {
        $data = $this->getBookData($abbreviation);
        if (array_key_exists('title', $data) !== true) {
            throw new ModelException('Book data for "'.$abbreviation.'" does not contain "title"', ModelException::CODE_STRUCTURE);
        }

        return $data['title'];

    }//end getBookName()


}//end class
