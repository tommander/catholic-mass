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

        $this->environment = file_get_contents(Helper::fullFilename($envFile));
        if ($this->environment === false) {
            throw new \Exception('File "'.$envFile.'" does not exist or cannot be read.');
        }

        $this->environment = \preg_replace('/\W/', '', $this->environment);
        if ($this->environment === '') {
            throw new \Exception('File "'.$envFile.'" contains an incorrect environment definition.');
        }

        $confFile = 'config/'.$this->environment.'.php';
        if (file_exists(Helper::fullFilename($confFile)) !== true) {
            throw new \Exception('Environment configuration "'.$confFile.'" file does not exist.');
        }

        // phpcs:ignore
        /** @psalm-suppress UnresolvableInclude */
        include_once $confFile;

    }//end __construct()


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
