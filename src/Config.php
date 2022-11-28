<?php
/**
 * Config unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Environment-based web app configuration
 */
class Config
{

    /**
     * Environment name
     *
     * @var string
     */
    private $environment = '';

    /**
     * Configuration items
     *
     * @var array
     */
    private $config = [];

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;


    /**
     * Constructor
     *
     * @param Logger $logger Logger instance
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        $envFile = '.env';
        if (file_exists(Helper::fullFilename($envFile)) !== true) {
            throw new \Exception('File "'.$envFile.'" does not exist.');
        }

        $this->environment = file_get_contents(Helper::fullFilename($envFile));
        if ($this->environment === false || $this->environment === '') {
            throw new \Exception('File "'.$envFile.'" does not exist or has an incorrect value.');
        }

        $this->environment = \preg_replace('/\W/', '', $this->environment);

        $confFile = 'config/'.$this->environment.'.json';
        if (file_exists(Helper::fullFilename($confFile)) !== true) {
            throw new \Exception('Environment configuration "'.$confFile.'" file does not exist.');
        }

        $this->config = Helper::loadJson($confFile);

    }//end __construct()


    /**
     * Reads a config item
     *
     * @param string $configKey Key in the associative array
     *
     * @return mixed
     */
    public function readConfig(string $configKey)
    {
        if (array_key_exists($configKey, $this->config) !== true) {
            return '';
        }

        return $this->config[$configKey];

    }//end readConfig()


    /**
     * Get environment value
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;

    }//end getEnvironment()


}//end class
