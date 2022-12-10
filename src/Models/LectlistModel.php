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

use TMD\OrderOfMass\Exceptions\ModelException;

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

        foreach ($copy as $sunday => $sunData) {
            if (is_string($sunday) !== true || is_array($sunData) !== true) {
                $this->logger->warning('Incorrect sunday code/data');
                continue;
            }

            $sundayClean = preg_replace('/[^A-Z0-9]/', '', $sunday);
            if ($sundayClean === '') {
                $this->logger->warning('Sunday code empty after cleaning');
                continue;
            }

            $this->jsonContent[$sundayClean] = [];
            foreach ($sunData as $cycle => $cycData) {
                if (in_array($cycle, ['A', 'B', 'C'], true) === true && is_array($cycData) === true) {
                    $this->jsonContent[$sundayClean][$cycle] = [];
                    foreach ($cycData as $read => $readData) {
                        if (in_array($read, ['r1', 'r2', 'p', 'a', 'g'], true) === true) {
                            if (is_string($readData) === true) {
                                $this->jsonContent[$sundayClean][$cycle][$read] = $readData;
                                continue;
                            }

                            if (is_array($readData) === true) {
                                $this->jsonContent[$sundayClean][$cycle][$read] = [];
                                foreach ($readData as $oneRead) {
                                    $this->jsonContent[$sundayClean][$cycle][$read][] = $oneRead;
                                }

                                continue;
                            }
                        }
                    }
                }
            }//end foreach
        }//end foreach

    }//end checkAndSanitize()


    /**
     * Hello
     *
     * @param string $sunday    Sunday abbreviation
     * @param string $yearCycle Year cycle (`A`, `B` or `C`)
     *
     * @return array
     */
    public function getReadings(string $sunday, string $yearCycle): array
    {
        if (is_array($this->jsonContent) !== true) {
            throw new ModelException('JSON content not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($sunday, $this->jsonContent) !== true) {
            throw new ModelException('Sunday "'.$sunday.'" not found', ModelException::CODE_PARAMETER);
        }

        if (is_array($this->jsonContent[$sunday]) !== true) {
            throw new ModelException('Sunday data for "'.$sunday.'" is not array', ModelException::CODE_STRUCTURE);
        }

        if (array_key_exists($yearCycle, $this->jsonContent[$sunday]) !== true) {
            throw new ModelException('Year cycle "'.$yearCycle.'" not found in sunday data for "'.$sunday.'"', ModelException::CODE_PARAMETER);
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
    public function getReading(string $sunday, string $yearCycle, string $reading): string|array
    {
        $readings = $this->getReadings($sunday, $yearCycle);
        if (array_key_exists($reading, $readings) !== true) {
            throw new ModelException('Reading "'.$reading.'" not found in year cycle "'.$yearCycle.'" sunday "'.$sunday.'"', ModelException::CODE_PARAMETER);
        }

        return $readings[$reading];

    }//end getReading()


}//end class
