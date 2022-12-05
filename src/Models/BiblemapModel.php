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
     * @return bool
     */
    public function load(string $file): bool
    {
        if ($file === '') {
            return false;
        }

        return $this->loadJson(sprintf('libs/zefania-bibles/map/%s', $file));

    }//end load()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return ?array
     */
    public function numberToData(int $num): ?array
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        $key = strval($num);
        if (array_key_exists($key, $this->jsonContent) !== true) {
            return null;
        }

        return $this->jsonContent[$key];

    }//end numberToData()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return ?string
     */
    public function numberToAbbreviation(int $num): ?string
    {
        $data = $this->numberToData($num);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('short', $data) !== true) {
            return null;
        }

        return $data['short'];

    }//end numberToAbbreviation()


    /**
     * Hello
     *
     * @param int $num Book number
     *
     * @return ?string
     */
    public function numberToName(int $num): ?string
    {
        $data = $this->numberToData($num);
        if ($data === null) {
            return null;
        }

        if (array_key_exists('full', $data) !== true) {
            return null;
        }

        return $data['full'];

    }//end numberToName()


}//end class
