<?php
/**
 * Logger unit
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\OrderOfMass;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Very simple logger to a text file.
 */
class Logger extends AbstractLogger
{


    /**
     * Actual log action, which saves the log item in the file `log.txt` in the root folder.
     *
     * The information included is:
     *
     * - Date (ISO format)
     * - Log level
     * - Log message
     *
     * @param mixed              $level   Log message level (as defined in {@see Psr\Log\Loglevel})
     * @param string|\Stringable $message Log message
     * @param array              $context Log message context
     *
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context=[]): void
    {
        if ($level !== LogLevel::EMERGENCY
            && $level !== LogLevel::ALERT
            && $level !== LogLevel::CRITICAL
            && $level !== LogLevel::ERROR
            && $level !== LogLevel::WARNING
            && $level !== LogLevel::NOTICE
            && $level !== LogLevel::INFO
            && $level !== LogLevel::DEBUG
        ) {
            throw new InvalidArgumentException('Invalid log level');
        }

        $logFile = __DIR__.'/../log.txt';

        $logHandle = fopen($logFile, 'a');
        if ($logHandle === false) {
            return;
        }

        try {
            if (is_string($message) === true) {
                fwrite($logHandle, sprintf("[%s]{%s} %s\r\n", date('c'), $level, $message));
            } else {
                fwrite($logHandle, sprintf("[%s]{%s} %s\r\n", date('c'), $level, $message->__toString()));
            }
        } finally {
            fclose($logHandle);
        }

    }//end log()


}//end class
