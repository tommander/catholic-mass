<?php
/**
 * Feedback form listener
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
 * Static functions to encrypt/decrypt text
 */
class Feedback
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
     * Hello
     *
     * @var CsrfProtection
     */
    private $csrfProtection;


    /**
     * Saves the instance of Logger
     *
     * @param Logger         $logger         Logger
     * @param Encryption     $encryption     Encryption
     * @param CsrfProtection $csrfProtection CsrfProtection
     */
    public function __construct(Logger $logger, Encryption $encryption, CsrfProtection $csrfProtection)
    {
        $this->logger         = $logger;
        $this->encryption     = $encryption;
        $this->csrfProtection = $csrfProtection;

    }//end __construct()


    /**
     * Prints out a JSON encoded status message
     *
     * @param bool   $success Success flag
     * @param int    $code    Status code
     * @param string $message Status message
     *
     * @return void
     */
    private function response(bool $success, int $code, string $message): void
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
     * Output an information about a single feedback JSON file
     *
     * This means a heading level 2 is printed out and then either a paragraph with error or pre tag with the decrypted content
     *
     * @param string $dir      File path
     * @param string $filename File name incl. extension
     * @param string $key      Decryption key
     *
     * @return void
     */
    private function outputFile(string $dir, string $filename, string $key): void
    {
        echo "<h2>File <q>$filename</q></h2>";

        $json = file_get_contents($dir.$filename);
        if ($json === false) {
            echo "<p><strong>ERROR: </strong>file does not exist or cannot be read</p>";
            return;
        }

        $jsonRaw = json_decode($json, true);
        if ($jsonRaw === false) {
            return;
        }

        if (array_key_exists('enc', $jsonRaw) !== true || array_key_exists('iv', $jsonRaw) !== true || array_key_exists('tag', $jsonRaw) !== true) {
            return;
        }

        $dec = $this->encryption->decrypt($jsonRaw['enc'], $key, $jsonRaw['iv'], $jsonRaw['tag']);
        if ($dec === null) {
            echo "<p><strong>ERROR: </strong>file cannot be decrypted</p>";
            return;
        }

        echo "<pre>$dec</pre>";

    }//end outputFile()


    /**
     * Listener to feedback store request
     *
     * @return void
     */
    public function saveFeedback()
    {
        if (array_key_exists('token', $_POST) !== true || is_string($_POST['token']) !== true) {
            header('HTTP/1.1 400 Bad Request');
            $this->response(false, 1, 'Missing or misformatted token');
            exit(1);
        }

        $val = $this->csrfProtection->validateCsrf($_POST['token']);
        if ($val !== 0) {
            header('HTTP/1.1 400 Bad Request');
            $errormsg = 'CSRF validation failed';
            switch ($val) {
            case 1:
                $errormsg = 'CSRF JSON structure is invalid. "'.$_POST['token'].'"';
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

            $this->response(false, 2, $errormsg);
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
            $this->response(false, 5, 'Form fields validation failed');
            exit(1);
        }

        $jsonStr = json_encode($fileData, JSON_PRETTY_PRINT);
        if ($jsonStr === false) {
            header('HTTP/1.1 500 Internal Server Error');
            $this->response(false, 3, 'JSON data preparation failed');
            exit(1);
        }

        // phpcs:ignore
        /** @psalm-suppress UndefinedConstant */
        $jsonEnc = $this->encryption->encrypt($jsonStr, JSON_KEY);
        if ($jsonEnc === null) {
            header('HTTP/1.1 500 Internal Server Error');
            $this->response(false, 4, 'Encryption failed');
            exit(1);
        }

        $jsonEncJson = json_encode($jsonEnc);
        if ($jsonEncJson === false) {
            header('HTTP/1.1 500 Internal Server Error');
            $this->response(false, 7, 'JSON data second preparation failed');
            exit(1);
        }

        $outFile = Helper::fullFilename('feedbacks/f'.time().'.json');

        if (file_put_contents($outFile, $jsonEncJson) === false) {
            header('HTTP/1.1 500 Internal Server Error');
            $this->response(false, 6, 'File was not saved');
            exit(1);
        }

        header('HTTP/1.1 200 OK');
        $this->response(true, 0, 'OK');

    }//end saveFeedback()


    /**
     * Output feedback files in decrypted form
     *
     * @return void
     */
    public function readFeedback()
    {
        if (array_key_exists('key', $_POST) !== true || is_string($_POST['key']) !== true) {
            echo '<form action="index.php?feedback=read" method="POST"><label for="KEY">Key:</label><br><input id="KEY" type="password" name="key"><br><input type="submit" value="OK"></form>';
        } else {
            $dir     = Helper::fullFilename('feedbacks/');
            $dirlist = scandir($dir);

            foreach ($dirlist as $onefile) {
                if (\str_starts_with($onefile, 'f') === true && \str_ends_with($onefile, '.json') === true) {
                    $this->outputFile($dir, $onefile, $_POST['key']);
                }
            }
        }

    }//end readFeedback()


}//end class
