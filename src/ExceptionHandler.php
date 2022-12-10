<?php
/**
 * Exception Handler unit
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
 * Handle exceptions
 */
class ExceptionHandler
{

    private const HTML_TEMPLATE = <<<EOS
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="assets/css/fonts.css">
            <link rel="stylesheet" href="libs/font-awesome/css/all.css">
            <title>@@TITLE@@</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                }
        
                body {
                    background-color: #eee;
                    font-family: "Source Sans Pro", sans-serif;
                } 
        
                header {
                    font-size: 150%;
                    font-weight: bold;
                    text-align: center;
                }
        
                header, footer, main {
                    margin: 0.5rem auto;
                    max-width: 1024px;
                    border: 1px solid black;
                    border-radius: 0.5rem;
                    padding: 1rem;
                    background-color: #fff;
                }

                p {
                    margin-bottom: 0.5rem;
                }

                ul {
                    margin-left: 1rem;
                }

                pre {
                    font-family: "Source Code Pro", monospace;
                    padding: 0.5rem;
                    background: #eee;
                    border: 1px solid black;
                    border-radius: 0.5rem;
                }

                code {
                    font-family: "Source Code Pro", monospace;
                }
            </style>
        </head>
        <body>
            <header>
                <i class="fa-solid fa-bug"></i> Error
            </header>
            <main>
                <p>Sorry, an unexpected error was caught.</p>
                <p>The report of the error was saved and we will start working on it soon.</p>
                <p>We would be really grateful if you could send us an e-mail to <a href="@@MAIL_LINK@@">@@MAIL_TEXT@@</a> with the below mentioned information. It will help us to find the reason and fix it quickly.</p>
                <pre>## Application
    Order of Mass

    ## Error ID
    @@HASH@@

    ## Time
    @@TIME@@
            
    ## What did I do before the error happened?
    1. Navigate to ...
    2. Click ...
    3. Select ...
    4. Write "xyz" to the field "abc"
            
    ## Additional info
    Write here some additional information if needed.
            
    ## Operating System
    Windows / Linux / MacOS / Android / iOS / ...
            
    ## Browser
    IE / Edge / Chrome / Firefox / Safari / Opera / Vivaldi / ...
            
    ## Device
    PC / Laptop / Tablet / Phone / ...
                
    ## Do I want to be informed back on the status of the issue?
    Yes / No</pre>
            </main>
        </body>
    </html>
    EOS;


    /**
     * Hello
     *
     * @param string $key Key
     *
     * @return string
     */
    private static function getServer(string $key): string
    {
        if (array_key_exists($key, $_SERVER) !== true) {
            return '';
        }

        if (is_string($_SERVER[$key]) !== true) {
            return '';
        }

        return $_SERVER[$key];

    }//end getServer()


    /**
     * Hello
     *
     * @param string $data Data to hash
     *
     * @return string
     */
    private static function doHash(string $data): string
    {
        if (\in_array('tiger128,3', \hash_algos()) !== true) {
            return '';
        }

        return \hash('tiger128,3', $data);

    }//end doHash()


    /**
     * Hello
     *
     * @return string
     */
    private static function fullUrl(): string
    {
        $scheme = self::getServer('REQUEST_SCHEME');
        $server = self::getServer('SERVER_NAME');
        $uri    = self::getServer('REQUEST_URI');
        $query  = self::getServer('QUERY_STRING');

        return sprintf('%s://%s%s%s', $scheme, $server, $uri, $query);

    }//end fullUrl()


    /**
     * Hello
     *
     * @param array $trace Trace
     *
     * @return array
     */
    private static function traceToArray(array $trace): array
    {
        $res = [];
        foreach ($trace as $num => $data) {
            $numKey = strval($num);

            $res[$numKey] = [];
            foreach ($data as $dataType => $dataContent) {
                if (($dataType === 'function' && is_string($dataContent) === true)
                    || ($dataType === 'line' && is_int($dataContent) === true)
                    || ($dataType === 'file' && is_string($dataContent) === true)
                    || ($dataType === 'class' && is_string($dataContent) === true)
                    || ($dataType === 'type' && is_string($dataContent) === true)
                ) {
                    $res[$numKey][$dataType] = $dataContent;
                } else if ($dataType === 'object' && is_object($dataContent) === true) {
                    $res[$numKey][$dataType] = var_export($dataContent, true);
                } else if ($dataType === 'args' && is_array($dataContent) === true) {
                    $res[$numKey][$dataType] = [];
                    foreach ($dataContent as $argNo => $argVal) {
                        $res[$numKey][$dataType][$argNo] = var_export($argVal, true);
                    }
                } else {
                    $res[$numKey][$dataType] = var_export($dataContent, true);
                }
            }
        }//end foreach

        return $res;

    }//end traceToArray()


    /**
     * Hello
     *
     * @param \Throwable|null $exc Throwable
     * @param array           $add Associative array of additional info (default `[]`)
     *
     * @return array|null
     */
    private static function exceptionToArray(?\Throwable $exc, array $add=[]): ?array
    {
        if ($exc === null) {
            return null;
        }

        $excArray = array_merge(
            $add,
            [
                'msg'      => $exc->getMessage(),
                'code'     => $exc->getCode(),
                'file'     => $exc->getFile(),
                'line'     => $exc->getLine(),
                'trace'    => self::traceToArray($exc->getTrace()),
                'previous' => self::exceptionToArray($exc->getPrevious()),
            ]
        );

        return $excArray;

    }//end exceptionToArray()


    /**
     * Hello
     *
     * @param \Throwable $exc Throwable
     *
     * @return void
     */
    public static function handleException(\Throwable $exc): void
    {
        try {
            $add = [
                'x_app'       => 'Order of Mass',
                'x_ip'        => self::doHash(self::getServer('REMOTE_ADDR')),
                'x_useragent' => self::getServer('HTTP_USER_AGENT'),
                'x_query'     => self::fullUrl(),
                'x_reqtime'   => self::getServer('REQUEST_TIME'),
                'x_time'      => time(),
                'x_class'     => $exc::class,
            ];

            $excHash  = '';
            $excArray = self::exceptionToArray($exc, $add);
            if ($excArray !== null) {
                $excReport = \json_encode($excArray, JSON_PRETTY_PRINT);
                if ($excReport !== false) {
                    $excHash  = self::doHash($excReport);
                    $rand     = bin2hex(\openssl_random_pseudo_bytes(4));
                    $filename = sprintf('%s/../errors/error_%d_%s_%s.json', __DIR__, time(), $excHash, $rand);
                    file_put_contents($filename, $excReport);
                }
            }

            echo str_replace(
                [
                    '@@TITLE@@',
                    '@@HASH@@',
                    '@@MAIL_TEXT@@',
                    '@@MAIL_LINK@@',
                    '@@TIME@@',
                ],
                [
                    'Exception',
                    $excHash,
                    'tommander@tommander.cz',
                    sprintf('mailto:tommander@tommander.cz?subject=%s', rawurlencode(sprintf('[%s] OrderOfMass Error', $excHash))),
                    date('c'),
                ],
                self::HTML_TEMPLATE
            );
        } catch (\Exception $e) {
            printf('Exception in ExceptionHandler: %s', $e->__toString());
        }//end try

    }//end handleException()


}//end class
