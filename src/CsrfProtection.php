<?php
/**
 * CSRF Protection unit
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
 * Static functions to generate/validate CSRF token
 */
class CsrfProtection
{


    /**
     * Lock file
     *
     * @return resource|null
     */
    private static function doLock()
    {
        $maxTime    = (time() + 15);
        $lockFile   = Helper::fullFilename('csrf.json');
        $lockHandle = \fopen($lockFile, 'c+');
        if ($lockHandle === false) {
            return null;
        }

        if (\flock($lockHandle, LOCK_EX) !== true) {
            fclose($lockHandle);
            return null;
        }

        return $lockHandle;

    }//end doLock()


    /**
     * Unlock file
     *
     * @param mixed $handle File handle
     *
     * @return bool
     */
    private static function doUnlock($handle): bool
    {
        if ($handle === false) {
            return false;
        }

        flock($handle, LOCK_UN);
        fclose($handle);
        return true;

    }//end doUnlock()


    /**
     * Load json
     *
     * @param mixed $handle File handle
     *
     * @return array
     */
    private static function loadCsrfJson($handle): array
    {
        if ($handle === false) {
            return [];
        }

        $raw = '';
        while (feof($handle) !== true) {
            $raw .= fread($handle, 8192);
        }

        $json = json_decode($raw, true);
        if (is_array($json) !== true) {
            return [];
        }

        return $json;

    }//end loadCsrfJson()


    /**
     * Save json
     *
     * @param mixed $handle File handle
     * @param array $data   Data to save
     *
     * @return void
     */
    private static function saveCsrfJson($handle, array $data)
    {
        if ($handle === false) {
            return;
        }

        \ftruncate($handle, 0);
        \rewind($handle);
        \fwrite($handle, json_encode($data));
        \fflush($handle);

    }//end saveCsrfJson()


    /**
     * Generate user data
     *
     * @return string|null
     */
    private static function generateUserData(): string|null
    {
        if (in_array('tiger192,4', \hash_algos()) !== true) {
            return null;
        }

        if (\array_key_exists('REMOTE_ADDR', $_SERVER) !== true) {
            return null;
        }

        $ipFiltered = \filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($ipFiltered === false) {
            return null;
        }

        return \hash('tiger192,4', $ipFiltered);

    }//end generateUserData()


    /**
     * Generates a CSRF token
     *
     * @return string|null
     */
    public static function generateCsrf(): string|null
    {
        $lockHandle = self::doLock();
        if ($lockHandle === null) {
            return null;
        }

        try {
            $csrfData = self::generateUserData();
            if ($csrfData === null) {
                return null;
            }

            $csrfToken = bin2hex(\openssl_random_pseudo_bytes(16));

            $json = self::loadCsrfJson($lockHandle);
            if (array_key_exists('tokens', $json) !== true) {
                $json['tokens'] = [];
            }

            if (array_key_exists('users', $json) !== true) {
                $json['users'] = [];
            }

            $json['tokens'][$csrfToken] = [
                'data' => $csrfData,
                'time' => time(),
            ];

            self::saveCsrfJson($lockHandle, $json);

            return $csrfToken;
        } finally {
            self::doUnlock($lockHandle);
        }//end try

    }//end generateCsrf()


    /**
     * Validate CSRF token
     *
     * @param string $csrfToken CSRF token
     *
     * @return int 0 on success, positive int on error
     */
    public static function validateCsrf(string $csrfToken): int
    {
        $lockHandle = self::doLock();
        if ($lockHandle === null) {
            return 4;
        }

        try {
            $json = self::loadCsrfJson($lockHandle);
            if (array_key_exists('tokens', $json) !== true
                || array_key_exists('users', $json) !== true
                || array_key_exists($csrfToken, $json['tokens']) !== true
                || array_key_exists('data', $json['tokens'][$csrfToken]) !== true
                || array_key_exists('time', $json['tokens'][$csrfToken]) !== true
            ) {
                return 1;
            }

            if (time() > ($json['tokens'][$csrfToken]['time'] + 1800)) {
                return 6;
            }

            $csrfData = self::generateUserData();
            if ($csrfData === null) {
                return 2;
            }

            if ($csrfData !== $json['tokens'][$csrfToken]['data']) {
                return 3;
            }

            if (array_key_exists($csrfData, $json['users']) === true && time() < ($json['users'][$csrfData] + 3600)) {
                return 5;
            }

            unset($json['tokens'][$csrfToken]);
            $json['users'][$csrfData] = time();
            self::saveCsrfJson($lockHandle, $json);

            return 0;
        } finally {
            self::doUnlock($lockHandle);
        }//end try

    }//end validateCsrf()


}//end class
