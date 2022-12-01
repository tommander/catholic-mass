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
    public const PARAM_LABELS     = 'll';
    public const PARAM_TEXTS      = 'tl';
    public const PARAM_BIBLE      = 'bl';
    public const PARAM_TYPE       = 'sn';
    public const PARAM_SELECTBOOK = 'book';

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
     * Universal function to retrieve a GET param
     *
     * If both array of values (`$values`) and regex (`$regex`) is handed over, the regex has priority
     *
     * @param string $name    Parameter name
     * @param string $default Parameter default value (if it cannot be read or has an incorrect value)
     * @param array  $values  Allowed values of the parameter (if its value is not in this array, the default value is returned)
     * @param string $regex   Regex for checking correct parameter value (if its value does not match the regex, the default value is returned)
     *
     * @return string
     */
    private function getParam(string $name, string $default, array $values=[], string $regex=''): string
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

    }//end getParam()


    /**
     * Retrieve labels language parameter (all texts outside of the text of the mass/rosary)
     *
     * @param string $default Default value
     *
     * @return string
     */
    public function getLabelLang(string $default='eng'): string
    {
        return $this->getParam(self::PARAM_LABELS, $default, [], '/^[a-z]{3}$/');

    }//end getLabelLang()


    /**
     * Retrieve text language parameter (text of the mass/rosary)
     *
     * @param string $default Default value
     *
     * @return string
     */
    public function getContentLang(string $default='eng'): string
    {
        return $this->getParam(self::PARAM_TEXTS, $default, [], '/^[a-z]{3}$/');

    }//end getContentLang()


    /**
     * Retrieve chosen Bible translation abbreviation
     *
     * @param string $default Default value
     *
     * @return string
     */
    public function getBible(string $default=''): string
    {
        return $this->getParam(self::PARAM_BIBLE, $default);

    }//end getBible()


    /**
     * Retrieve page type (mass/rosary/Bible)
     *
     * @param string $default Default value
     *
     * @return string
     */
    public function getType(string $default='mass'): string
    {
        return $this->getParam(self::PARAM_TYPE, $default, ['mass', 'rosary', 'bible']);

    }//end getType()


    /**
     * Retrieve the book within Bible, that the user currently wants to read
     *
     * @param string $default Default value
     *
     * @return string
     */
    public function getSelectBook(string $default=''): string
    {
        return $this->getParam(self::PARAM_SELECTBOOK, $default);

    }//end getSelectBook()


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
     * Checks whether Bible was chosen as the content type
     *
     * @return bool
     */
    public function isBible()
    {
        return ($this->getType() === 'bible');

    }//end isBible()


    /**
     * Feedback write request (this means the JS script sent an asynchronous request to store the feedback data)
     *
     * @return bool
     */
    public function isFeedbackWrite()
    {
        return ($this->getParam('feedback', '') === 'write');

    }//end isFeedbackWrite()


    /**
     * Feedback read request (this is when we want to show decrypted feedback data)
     *
     * @return bool
     */
    public function isFeedbackRead()
    {
        return ($this->getParam('feedback', '') === 'read');

    }//end isFeedbackRead()


}//end class
