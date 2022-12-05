<?php
/**
 * Base Model
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace TMD\OrderOfMass\Models;

use TMD\OrderOfMass\Helper;
use TMD\OrderOfMass\Logger;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Hello
 */
abstract class BaseModel
{

    /**
     * Hello
     *
     * @var mixed
     */
    protected $jsonContent;

    /**
     * Hello
     *
     * @var string
     */
    protected $jsonFilename;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;


    /**
     * Hello
     *
     * @return void
     */
    abstract protected function initModel();


    /**
     * Save service instances
     *
     * @param Logger $logger Logger service
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        $this->jsonContent  = null;
        $this->jsonFilename = '';

        $this->initModel();

    }//end __construct()


    /**
     * Loads a JSON file into a PHP-friendly structure
     *
     * Basically a tiny little wrapper around {@see \json_decode()}.
     *
     * @param string $fileName Path to the JSON file relative to the root of the workspace
     * @param bool   $assoc    JSON objects will be converted to associative arrays instead of objects (default: `true`)
     *
     * @return bool
     */
    protected function loadJson(string $fileName, bool $assoc=true): bool
    {
        $this->jsonContent  = null;
        $this->jsonFilename = '';
        if ($fileName === '') {
            return false;
        }

        $this->jsonFilename = Helper::fullFilename($fileName);

        if (file_exists($this->jsonFilename) !== true) {
            return false;
        }

        $tmpContent = file_get_contents($this->jsonFilename);
        if ($tmpContent === false) {
            return false;
        }

        $this->jsonContent = json_decode($tmpContent, $assoc);
        return true;

    }//end loadJson()


    /**
     * Saves the JSON content to the file, from which it was previously loaded
     *
     * @param bool $prettyPrint Whether to `pretty_print` the resulting JSON (default: `false`)
     *
     * @return bool
     */
    protected function saveJson(bool $prettyPrint=false): bool
    {
        $options = 0;
        if ($prettyPrint === true) {
            $options = JSON_PRETTY_PRINT;
        }

        $tmpContent = json_encode($this->jsonContent, $options);
        if ($tmpContent === false) {
            return false;
        }

        $res = file_put_contents($this->jsonFilename, $tmpContent);
        return ($res !== false);

    }//end saveJson()


}//end class
