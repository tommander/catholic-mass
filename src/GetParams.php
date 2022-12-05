<?php
/**
 * GetParams unit
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
 * Checks and reads all possible GET parameters.
 */
class GetParams
{
    public const PARAM_LABELS = 'labels';
    public const PARAM_TEXTS  = 'content';
    public const PARAM_BIBLE  = 'bible';
    public const PARAM_TYPE   = 'type';
    public const PARAM_BOOK   = 'book';

    public const TYPE_MASS   = 'mass';
    public const TYPE_ROSARY = 'rosary';
    public const TYPE_BIBLE  = 'bible';

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Sanitized GET params
     *
     * @var array
     */
    private $params = [];


    /**
     * Saves the instance of Logger
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->params = [
            self::PARAM_LABELS => $this->readParam(self::PARAM_LABELS, 'eng', [], '/^[a-z]{3}$/'),
            self::PARAM_TEXTS  => $this->readParam(self::PARAM_TEXTS, 'eng', [], '/^[a-z]{3}$/'),
            self::PARAM_BIBLE  => $this->readParam(self::PARAM_BIBLE, ''),
            self::PARAM_TYPE   => $this->readParam(self::PARAM_TYPE, self::TYPE_MASS, [self::TYPE_MASS, self::TYPE_ROSARY, self::TYPE_BIBLE]),
            self::PARAM_BOOK   => $this->readParam(self::PARAM_BOOK, ''),
        ];

    }//end __construct()


    /**
     * Universal function to read a GET param
     *
     * If both array of values (`$values`) and regex (`$regex`) is handed over, the regex has priority
     *
     * Note that the value of the param must be a string and that it is first `urldecoded` before other checks are performed (if any)
     *
     * @param string $name    Parameter name
     * @param string $default Parameter default value (if it cannot be read or has an incorrect value)
     * @param array  $values  Allowed values of the parameter (if its value is not in this array, the default value is returned)
     * @param string $regex   Regex for checking correct parameter value (if its value does not match the regex, the default value is returned)
     *
     * @return string
     */
    private function readParam(string $name, string $default, array $values=[], string $regex=''): string
    {
        if (isset($_GET[$name]) !== true || is_string($_GET[$name]) !== true) {
            return $default;
        }

        $val = \urldecode($_GET[$name]);

        if ($regex !== '' && \preg_match($regex, $val) !== 1) {
            return $default;
        }

        if ((count($values) > 0) && in_array($val, $values) !== true) {
            return $default;
        }

        return $val;

    }//end readParam()


    /**
     * Gets the sanitized value of a GET param
     *
     * @param string $paramName One of the `PARAM_` public constants
     *
     * @return string
     */
    public function getParam(string $paramName): string
    {
        if (array_key_exists($paramName, $this->params) !== true) {
            return '';
        }

        return $this->params[$paramName];

    }//end getParam()


}//end class
