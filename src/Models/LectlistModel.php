<?php
/**
 * Lectlist.json Model Unit
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
 * Lectlist.json Model
 */
class LectlistModel extends BaseModel
{


    /**
     * Initialization called at the end of the constructor
     *
     * @return void
     */
    protected function initModel()
    {
        $this->loadJson('assets/json/lectlist.json');

    }//end initModel()


    /**
     * Hello
     *
     * @param string $sunday    Sunday abbreviation
     * @param string $yearCycle Year cycle (`A`, `B` or `C`)
     *
     * @return ?array
     */
    public function getReadings(string $sunday, string $yearCycle): ?array
    {
        if (is_array($this->jsonContent) !== true) {
            return null;
        }

        if (array_key_exists($sunday, $this->jsonContent) !== true) {
            return null;
        }

        if (is_array($this->jsonContent[$sunday]) !== true) {
            return null;
        }

        if (array_key_exists($yearCycle, $this->jsonContent[$sunday]) !== true) {
            return null;
        }

        return $this->jsonContent[$sunday][$yearCycle];

    }//end getReadings()


    /**
     * Hello
     *
     * @param string $sunday    Sunday abbreviation
     * @param string $yearCycle Year cycle (`A`, `B` or `C`)
     * @param string $reading   Reading (`r1`, `r2`, `p`, `a` or `g`)
     *
     * @return string
     */
    public function getReading(string $sunday, string $yearCycle, string $reading): string|array|null
    {
        $readings = $this->getReadings($sunday, $yearCycle);
        if ($readings === null) {
            return null;
        }

        if (array_key_exists($reading, $readings) !== true) {
            return null;
        }

        return $readings[$reading];

    }//end getReading()


}//end class
