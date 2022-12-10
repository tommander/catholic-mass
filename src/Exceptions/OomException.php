<?php
/**
 * Base exception unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace TMD\OrderOfMass\Exceptions;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Base Exception for Order of Mass
 */
class OomException extends \Exception
{

}//end class
