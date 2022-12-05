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
     * @return bool
     */
    public function load(string $languageCode): bool
    {
        return $this->loadJson(sprintf('assets/json/lang/%s-content.json', $languageCode), false);

    }//end load()


    /**
     * Hello
     *
     * @return ?array
     */
    public function getMass(): ?array
    {
        if (is_object($this->jsonContent) !== true) {
            return null;
        }

        if (property_exists($this->jsonContent, 'mass') !== true) {
            return null;
        }

        return $this->jsonContent->mass;

    }//end getMass()


    /**
     * Hello
     *
     * @return ?array
     */
    public function getRosary(): ?array
    {
        if (is_object($this->jsonContent) !== true) {
            return null;
        }

        if (property_exists($this->jsonContent, 'rosary') !== true) {
            return null;
        }

        return $this->jsonContent->rosary;

    }//end getRosary()


}//end class
