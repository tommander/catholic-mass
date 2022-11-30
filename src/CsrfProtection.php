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
     * Hello
     *
     * @var Logger
     */
    private $logger;

    /**
     * Hello
     *
     * @var Encryption
     */
    private $encryption;


    /**
     * Saves the instance of Logger
     *
     * @param Logger     $logger     Logger
     * @param Encryption $encryption Encryption
     */
    public function __construct(Logger $logger, Encryption $encryption)
    {
        $this->logger     = $logger;
        $this->encryption = $encryption;

    }//end __construct()


    /**
     * Lock file
     *
     * @return resource|null
     */
    private function doLock()
    {
        $maxTime    = (time() + 15);
        $lockFile   = Helper::fullFilename('feedbacks/_csrf.json');
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
    private function doUnlock($handle): bool
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
    private function loadCsrfJson($handle): array
    {
        if ($handle === false) {
            return [];
        }

        $raw = '';
        while (feof($handle) !== true) {
            $raw .= fread($handle, 8192);
        }

        if ($raw === '') {
            return [];
        }

        $rawJson = json_decode($raw, true);
        if (is_array($rawJson) !== true) {
            return [];
        }

        if (array_key_exists('enc', $rawJson) !== true || array_key_exists('iv', $rawJson) !== true || array_key_exists('tag', $rawJson) !== true) {
            return [];
        }

        // phpcs:ignore
        /** @psalm-suppress UndefinedConstant */
        $rawDec = $this->encryption->decrypt($rawJson['enc'], CSRF_KEY, $rawJson['iv'], $rawJson['tag']);
        if ($rawDec === null) {
            return [];
        }

        $json = json_decode($rawDec, true);
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
     * @return bool
     */
    private function saveCsrfJson($handle, array $data): bool
    {
        if ($handle === false) {
            return false;
        }

        $dataJson = json_encode($data);
        if ($dataJson === false) {
            return false;
        }

        // phpcs:ignore
        /** @psalm-suppress UndefinedConstant */
        $encData = $this->encryption->encrypt($dataJson, CSRF_KEY);
        if ($encData === null) {
            return false;
        }

        $encDataJson = json_encode($encData);
        if ($encDataJson === false) {
            return false;
        }

        \ftruncate($handle, 0);
        \rewind($handle);
        \fwrite($handle, $encDataJson);
        \fflush($handle);
        return true;

    }//end saveCsrfJson()


    /**
     * Generate user data
     *
     * @return string|null
     */
    private function generateUserData(): string|null
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
    public function generateCsrf(): string|null
    {
        $lockHandle = $this->doLock();
        if ($lockHandle === null) {
            return null;
        }

        try {
            $csrfData = $this->generateUserData();
            if ($csrfData === null) {
                return null;
            }

            $csrfToken = bin2hex(\openssl_random_pseudo_bytes(16));

            $json = $this->loadCsrfJson($lockHandle);
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

            $this->saveCsrfJson($lockHandle, $json);

            return $csrfToken;
        } finally {
            $this->doUnlock($lockHandle);
        }//end try

    }//end generateCsrf()


    /**
     * Validate CSRF token
     *
     * @param string $csrfToken CSRF token
     *
     * @return int 0 on success, positive int on error
     */
    public function validateCsrf(string $csrfToken): int
    {
        $lockHandle = $this->doLock();
        if ($lockHandle === null) {
            return 4;
        }

        try {
            $json = $this->loadCsrfJson($lockHandle);
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

            $csrfData = $this->generateUserData();
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
            $this->saveCsrfJson($lockHandle, $json);

            return 0;
        } finally {
            $this->doUnlock($lockHandle);
        }//end try

    }//end validateCsrf()


}//end class
