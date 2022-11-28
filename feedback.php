<?php
/**
 * Feedback form listener
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

define('OOM_BASE', 'orderofmass');
require __DIR__.'/src/Helper.php';
require __DIR__.'/src/CsrfProtection.php';


/**
 * Prints out a JSON encoded status message
 *
 * @param bool   $success Success flag
 * @param int    $code    Status code
 * @param string $message Status message
 *
 * @return void
 */
function response(bool $success, int $code, string $message): void
{
    echo json_encode(
        [
            'success' => $success,
            'code'    => $code,
            'message' => $message,
        ]
    );

}//end response()


/**
 * Encrypts a text with public key, whose filename is found in the file `./feedbacks/_pub`, and return the encrypted message base64 encoded
 *
 * @param string $plainText Text to encrypt
 *
 * @return string
 */
function encryptIt(string $plainText): string|null
{
    $dir       = __DIR__.'/feedbacks/';
    $indexFile = file_get_contents($dir.'_pub');
    if ($indexFile === false) {
        return null;
    }

    $pubkey = file_get_contents($dir.$indexFile);
    if ($pubkey === false) {
        return null;
    }

    if (openssl_public_encrypt($plainText, $res, $pubkey) === true) {
        return base64_encode($res);
    }

    return null;

}//end encryptIt()


if (array_key_exists('token', $_POST) !== true || is_string($_POST['token']) !== true) {
    header('HTTP/1.1 400 Bad Request');
    response(false, 1, 'Missing or misformatted token');
    exit(1);
}

$val = TMD\OrderOfMass\CsrfProtection::validateCsrf($_POST['token']);
if ($val !== 0) {
    header('HTTP/1.1 400 Bad Request');
    $errormsg = 'CSRF validation failed';
    switch ($val) {
    case 1:
        $errormsg = 'CSRF JSON structure is invalid.';
        break;
    case 2:
        $errormsg = 'No user data was generated.';
        break;
    case 3:
        $errormsg = 'Current user data is different from token user data.';
        break;
    case 4:
        $errormsg = 'CSRF JSON was not locked.';
        break;
    case 5:
        $errormsg = 'You can submit only one feedback per hour.';
        break;
    case 6:
        $errormsg = 'Token is older than 30 minutes.';
        break;
    }

    response(false, 2, $errormsg);
    exit(1);
}//end if

$fileData = [];

$fileData['email'] = null;
if (isset($_POST['email']) === true) {
    $emailFiltered = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if ($emailFiltered !== false) {
        $fileData['email'] = $emailFiltered;
    }
}

$fileData['rating'] = null;
if (isset($_POST['rating']) === true) {
    $ratingFiltered     = intval($_POST['rating']);
    $fileData['rating'] = $ratingFiltered;
}

$fileData['description'] = null;
if (isset($_POST['description']) === true) {
    $descFiltered = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($descFiltered !== false) {
        $fileData['description'] = $descFiltered;
    }
}

$fileData['rules'] = null;
if (isset($_POST['rules']) === true && $_POST['rules'] === 'ok') {
    $fileData['rules'] = true;
}

if ($fileData['rules'] !== true || $fileData['description'] === null) {
    header('HTTP/1.1 400 Bad Request');
    response(false, 5, 'Form fields validation failed');
    exit(1);
}

$jsonStr = json_encode($fileData, JSON_PRETTY_PRINT);
if ($jsonStr === false) {
    header('HTTP/1.1 500 Internal Server Error');
    response(false, 3, 'JSON data preparation failed');
    exit(1);
}

$jsonEnc = encryptIt($jsonStr);
if ($jsonEnc === null) {
    header('HTTP/1.1 500 Internal Server Error');
    response(false, 4, 'Encryption failed');
    exit(1);
}

$outFile = __DIR__.'/feedbacks/f'.time().'.json';

if (file_put_contents($outFile, $jsonEnc) === false) {
    header('HTTP/1.1 500 Internal Server Error');
    response(false, 6, 'File was not saved');
    exit(1);
}

header('HTTP/1.1 200 OK');
response(true, 0, 'OK');
