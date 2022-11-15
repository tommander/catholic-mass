<?php
/**
 * GetParams unit
 *
 * PHP version 7.4
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
    public const PARAM_LABELS = 'll';
    public const PARAM_TEXTS  = 'tl';
    public const PARAM_BIBLE  = 'bl';
    public const PARAM_TYPE   = 'sn';

    /**
     * Hello
     *
     * @var Logger
     */
    private $logger;


    /**
     * Saves the instance of Logger
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

    }//end __construct()


    /**
     * Hello
     *
     * @param string $name    Hello
     * @param string $default Hello
     * @param array  $values  Hello
     * @param string $regex   Hello
     *
     * @return string
     */
    private function getParam(string $name, string $default, array $values=[], string $regex=''): string
    {
        if (isset($_GET[$name]) !== true || is_string($_GET[$name]) !== true) {
            return $default;
        }

        $val = \htmlspecialchars($_GET[$name]);

        if ($regex !== '' && \preg_match($regex, $val) !== 1) {
            return $default;
        }

        if ((count($values) > 0) && in_array($val, $values) !== true) {
            return $default;
        }

        return $val;

    }//end getParam()


    /**
     * Hello
     *
     * @param string $default Default
     *
     * @return string
     */
    public function getLabelLang(string $default='eng'): string
    {
        return $this->getParam(self::PARAM_LABELS, $default, [], '/^[a-z]{3}$/');

    }//end getLabelLang()


    /**
     * Hello
     *
     * @param string $default Default
     *
     * @return string
     */
    public function getContentLang(string $default='eng'): string
    {
        return $this->getParam(self::PARAM_TEXTS, $default, [], '/^[a-z]{3}$/');

    }//end getContentLang()


    /**
     * Hello
     *
     * @param string $default Default
     *
     * @return string
     */
    public function getBible(string $default=''): string
    {
        return $this->getParam(self::PARAM_BIBLE, $default, [], '/^[a-z]{2,10}$/');

    }//end getBible()


    /**
     * Hello
     *
     * @param string $default Default
     *
     * @return string
     */
    public function getType(string $default='mass'): string
    {
        return $this->getParam(self::PARAM_TYPE, $default, ['mass', 'rosary']);

    }//end getType()


    /**
     * Checks whether rosary was chosen as the content type
     *
     * @return bool
     */
    public function isRosary()
    {
        return ($this->getType() === 'rosary');

    }//end isRosary()


    /**
     * Whether to run indices builder
     *
     * @return bool
     */
    public function isBuilder(): bool
    {
        return (isset($_GET['build']) === true && $_GET['build'] === 'indices');

    }//end isBuilder()


}//end class
