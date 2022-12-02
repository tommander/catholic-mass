<?php
/**
 * Encryption unit
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
 * Symmetric encryption/decryption functionality
 */
class Encryption
{

    /**
     * Logger service
     *
     * @var Logger
     */
    private $logger;


    /**
     * Save service instances
     *
     * @param Logger $logger Logger service
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

    }//end __construct()


    /**
     * Encrypts a text with the delivered public key and return the encrypted message base64 encoded along with IV and tag (both converted to hexadecimal value)
     *
     * @param string $plainText Text to encrypt
     * @param string $key       Raw encryption key
     *
     * @return array|null
     */
    public function encrypt(string $plainText, string $key): array|null
    {
        $method = 'aes-128-gcm';
        if (in_array($method, openssl_get_cipher_methods()) !== true) {
            return null;
        }

        $ivlen = openssl_cipher_iv_length($method);
        $iv    = openssl_random_pseudo_bytes($ivlen);
        $enc   = openssl_encrypt($plainText, $method, $key, 0, $iv, $tag);
        if ($enc === false) {
            return null;
        }

        return [
            'enc' => base64_encode($enc),
            'iv'  => bin2hex($iv),
            'tag' => bin2hex($tag),
        ];

    }//end encrypt()


    /**
     * Decrypt a base64 encoded message with previously generated IV and tag and using the delivered decryption key
     *
     * @param string $base64text Encrypted base64 encoded text
     * @param string $key        Raw decryption key
     * @param string $iv         Hexadecimal IV
     * @param string $tag        Hexadecimal tag
     *
     * @return ?string
     */
    public function decrypt(string $base64text, string $key, string $iv, string $tag): ?string
    {
        $method  = 'aes-128-gcm';
        $rawText = base64_decode($base64text);
        if ($rawText === false) {
            return null;
        }

        $rawIv  = hex2bin($iv);
        $rawTag = hex2bin($tag);

        $dec = \openssl_decrypt($rawText, $method, $key, 0, $rawIv, $rawTag);
        if ($dec === false) {
            return null;
        }

        return $dec;

    }//end decrypt()


}//end class
