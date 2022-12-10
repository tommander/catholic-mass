<?php
/**
 * Base model exception unit
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
 * Base Model Exception for Order of Mass
 */
class ModelException extends \Exception
{

    public const CODE_FILENAME    = 1;
    public const CODE_FILECONTENT = 2;
    public const CODE_STRUCTURE   = 3;
    public const CODE_PARAMETER   = 4;

}//end class
