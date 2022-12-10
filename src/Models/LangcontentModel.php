<?php
/**
 * Xxx-content.json Model Unit
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
 * Xxx-content.json Model
 */
class LangcontentModel extends BaseModel
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
        $this->loadJson(sprintf('assets/json/lang/%s-content.json', $languageCode), false);
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
        if (is_object($this->jsonContent) !== true) {
            $this->logger->warning('No content');
            return;
        }

        $copy = $this->jsonContent;
        $this->jsonContent = new \stdClass();

        if (property_exists($copy, 'mass') === true && is_array($copy->mass) === true) {
            $this->jsonContent->mass = [];
            foreach ($copy->mass as $row) {
                if (is_object($row) === true
                    && ((property_exists($row, '') === true && is_string($row->{''}) === true)
                    || (property_exists($row, 'p') === true && is_string($row->p) === true)
                    || (property_exists($row, 'a') === true && is_string($row->a) === true)
                    || (property_exists($row, 'r') === true && is_string($row->r) === true)
                    || (property_exists($row, 'read') === true && in_array($row->read, ['r1', 'r2', 'p', 'a', 'g'], true) === true)
                    || (property_exists($row, 'reading') === true && $row->reading === ''))
                ) {
                    $this->jsonContent->mass[] = $row;
                    continue;
                }

                if (is_array($row) === true) {
                    $arr = [];
                    foreach ($row as $rowItem) {
                        if (is_object($rowItem) === true
                            && property_exists($rowItem, 'title') === true
                            && is_string($rowItem->title) === true
                            && property_exists($rowItem, 'content') === true
                            && is_array($rowItem->content) === true
                        ) {
                            $arr[] = $rowItem;
                        }
                    }

                    $this->jsonContent->mass[] = $arr;
                }
            }//end foreach
        }//end if

        if (property_exists($copy, 'rosary') === true && is_array($copy->rosary) === true) {
            $this->jsonContent->rosary = [];
            foreach ($copy->rosary as $row) {
                if (is_object($row) === true
                    && ((property_exists($row, '') === true && is_string($row->{''}) === true)
                    || (property_exists($row, 'p') === true && is_string($row->p) === true)
                    || (property_exists($row, 'a') === true && is_string($row->a) === true)
                    || (property_exists($row, 'r') === true && is_string($row->r) === true)
                    || (property_exists($row, 'read') === true && in_array($row->read, ['r1', 'r2', 'p', 'a', 'g'], true) === true)
                    || (property_exists($row, 'reading') === true && $row->reading === ''))
                ) {
                    $this->jsonContent->rosary[] = $row;
                    continue;
                }

                if (is_array($row) === true) {
                    $arr = [];
                    foreach ($row as $rowItem) {
                        if (is_object($rowItem) === true
                            && property_exists($rowItem, 'title') === true
                            && is_string($rowItem->title) === true
                            && property_exists($rowItem, 'content') === true
                            && is_array($rowItem->content) === true
                        ) {
                            $arr[] = $row;
                        }
                    }

                    $this->jsonContent->rosary[] = $arr;
                }
            }//end foreach
        }//end if

    }//end checkAndSanitize()


    /**
     * Hello
     *
     * @return array
     */
    public function getMass(): array
    {
        if (is_object($this->jsonContent) !== true) {
            throw new ModelException('JSON content not object', ModelException::CODE_STRUCTURE);
        }

        if (property_exists($this->jsonContent, 'mass') !== true) {
            throw new ModelException('JSON content does not contain "mass" property', ModelException::CODE_STRUCTURE);
        }

        return $this->jsonContent->mass;

    }//end getMass()


    /**
     * Hello
     *
     * @return array
     */
    public function getRosary(): array
    {
        if (is_object($this->jsonContent) !== true) {
            throw new ModelException('JSON content not object', ModelException::CODE_STRUCTURE);
        }

        if (property_exists($this->jsonContent, 'rosary') !== true) {
            throw new ModelException('JSON content does not contain "rosary" property', ModelException::CODE_STRUCTURE);
        }

        return $this->jsonContent->rosary;

    }//end getRosary()


}//end class
