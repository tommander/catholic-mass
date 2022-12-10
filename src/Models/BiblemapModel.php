<?php
/**
 * Zefania-bibles map Model Unit
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
 * Zefania-bibles map Model
 */
class BiblemapModel extends BaseModel
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

        $this->loadJson(sprintf('libs/zefania-bibles/map/%s', $file));
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

        foreach ($copy as $bookNum => $bookData) {
            if (is_string($bookNum) !== true || is_array($bookData) !== true) {
                $this->logger->warning('BookNum/bookData incorrect');
                continue;
            }

            $bookNumClean = preg_replace('/[^0-9]/', '', $bookNum);
            if ($bookNumClean === '') {
                $this->logger->warning('BookNum empty after cleaning');
                continue;
            }

            $this->jsonContent[$bookNumClean] = [];
            foreach ($bookData as $dataKey => $dataValue) {
                if ($dataKey === 'full' && is_string($dataValue) === true) {
                    $this->jsonContent[$bookNumClean]['full'] = \htmlspecialchars($dataValue);
                    continue;
                }

                if ($dataKey === 'short' && is_string($dataValue) === true) {
                    $this->jsonContent[$bookNumClean]['short'] = \htmlspecialchars($dataValue);
                    continue;
                }

                $this->logger->warning('Unknown key "'.$dataKey.'" with value "'.var_export($dataValue, true).'"');
            }
        }//end foreach

    }//end checkAndSanitize()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return array
     */
    public function numberToData(int $num): array
    {
        if (is_array($this->jsonContent) !== true) {
            return [];
        }

        $key = strval($num);
        if (array_key_exists($key, $this->jsonContent) !== true) {
            return [];
        }

        return $this->jsonContent[$key];

    }//end numberToData()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return string
     */
    public function numberToAbbreviation(int $num): string
    {
        $data = $this->numberToData($num);
        if (array_key_exists('short', $data) !== true) {
            return '';
        }

        return $data['short'];

    }//end numberToAbbreviation()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return string
     */
    public function numberToName(int $num): string
    {
        $data = $this->numberToData($num);
        if (array_key_exists('full', $data) !== true) {
            return '';
        }

        return $data['full'];

    }//end numberToName()


}//end class
