<?php
/**
 * Feedback form reader
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php


/**
 * Decrypt a base64 encoded message using the delivered private key
 *
 * @param string $base64text Encrypted base64 encoded text
 * @param string $privkey    Private key in PEM format
 *
 * @return string
 */
function decryptIt(string $base64text, string $privkey): string|null
{
    $dir       = __DIR__.'/feedbacks/';
    $indexFile = file_get_contents($dir.'_pub');
    if ($indexFile === false) {
        echo "<p><strong>ERROR: </strong>index file for public key does not exist or cannot be read</p>";
        return null;
    }

    if (openssl_private_decrypt(base64_decode($base64text), $res, $privkey) === true) {
        return $res;
    }

    echo "<p><strong>ERROR: </strong>decryption failed - <q>".openssl_error_string()."</q></p>";
    return null;

}//end decryptIt()


/**
 * Output an information about a single feedback JSON file
 *
 * This means a heading level 2 is printed out and then either a paragraph with error or pre tag with the decrypted content
 *
 * @param string $dir      File path
 * @param string $filename File name incl. extension
 * @param string $privkey  Private key for decryption in PEM format
 *
 * @return void
 */
function outputFile(string $dir, string $filename, string $privkey): void
{
    echo "<h2>File <q>$filename</q></h2>";

    $json = file_get_contents($dir.$filename);
    if ($json === false) {
        echo "<p><strong>ERROR: </strong>file does not exist or cannot be read</p>";
        return;
    }

    $dec = decryptIt($json, $privkey);
    if ($dec === null) {
        echo "<p><strong>ERROR: </strong>file cannot be decrypted</p>";
        return;
    }

    echo "<pre>$dec</pre>";

}//end outputFile()


if (array_key_exists('privkey', $_POST) !== true || is_string($_POST['privkey']) !== true) {
    echo '<form action="feedback_read.php" method="POST"><label for="PRIV_KEY">Private key:</label><br><textarea id="PRIV_KEY" name="privkey"></textarea><br><input type="submit" value="OK"></form>';
    return;
}

$dir     = __DIR__.'/feedbacks/';
$dirlist = scandir($dir);

foreach ($dirlist as $onefile) {
    if (str_ends_with($onefile, '.json') === true) {
        outputFile($dir, $onefile, $_POST['privkey']);
    }
}

?>
</body>
</html>
