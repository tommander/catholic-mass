<?php
/**
 * GetParams unit
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
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

    const PARAM_LABELS = 'll';
    const PARAM_TEXTS  = 'tl';
    const PARAM_BIBLE  = 'bl';
    const PARAM_TYPE   = 'sn';

    /**
     * Hello
     *
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Saves the instance of Logger
     *
     * @param LoggerInterface $logger Logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

    }//end __construct()


    /**
     * Hello
     *
     * @param string $name    Hello
     * @param string $default Hello
     * @param array  $values  Hello
     *
     * @return string
     */
    private function getParam(string $name, string $default, array $values=[]): string
    {
        if (isset($_GET[$name]) !== true) {
            return $default;
        }

        $val = $_GET[$name];

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
        return $this->getParam(self::PARAM_LABELS, $default);

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
        return $this->getParam(self::PARAM_TEXTS, $default);

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
        return $this->getParam(self::PARAM_BIBLE, $default);

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


}//end class
