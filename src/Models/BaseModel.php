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

use TMD\OrderOfMass\Exceptions\ModelException;
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
     * @return void
     */
    protected function loadJson(string $fileName, bool $assoc=true): void
    {
        $this->jsonContent  = null;
        $this->jsonFilename = '';
        if ($fileName === '') {
            throw new ModelException('Empty filename', ModelException::CODE_FILENAME);
        }

        $this->jsonFilename = Helper::fullFilename($fileName);

        if (file_exists($this->jsonFilename) !== true) {
            throw new ModelException('File "'.$this->jsonFilename.'" does not exist', ModelException::CODE_FILENAME);
        }

        $tmpContent = file_get_contents($this->jsonFilename);
        if ($tmpContent === false) {
            throw new ModelException('Cannot load file content', ModelException::CODE_FILECONTENT);
        }

        $this->jsonContent = json_decode($tmpContent, $assoc);

    }//end loadJson()


    /**
     * Saves the JSON content to the file, from which it was previously loaded
     *
     * @param bool $prettyPrint Whether to `pretty_print` the resulting JSON (default: `false`)
     *
     * @return void
     */
    protected function saveJson(bool $prettyPrint=false): void
    {
        $options = 0;
        if ($prettyPrint === true) {
            $options = JSON_PRETTY_PRINT;
        }

        $tmpContent = json_encode($this->jsonContent, $options);
        if ($tmpContent === false) {
            throw new ModelException('Cannot encode file content', ModelException::CODE_FILECONTENT);
        }

        $res = file_put_contents($this->jsonFilename, $tmpContent);
        if ($res === false) {
            throw new ModelException('Cannot save file', ModelException::CODE_FILECONTENT);
        }

    }//end saveJson()


}//end class
