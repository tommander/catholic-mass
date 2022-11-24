<?php
    function encryptIt(string $plainText): string|null {
        $dir = __DIR__.'/feedbacks/';
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
    }

    $fileData = [];
    if (isset($_POST['email'])) {
        $fileData['email'] = $_POST['email'];
    } else {
        $fileData['email'] = null;
    }
    if (isset($_POST['rating'])) {
        $fileData['rating'] = $_POST['rating'];
    } else {
        $fileData['rating'] = null;
    }
    if (isset($_POST['description'])) {
        $fileData['description'] = $_POST['description'];
    } else {
        $fileData['description'] = null;
    }
    $jsonStr = json_encode($fileData, JSON_PRETTY_PRINT);
    if ($jsonStr === false) {
        exit(1);
    }
    $jsonEnc = encryptIt($jsonStr);
    if ($jsonEnc === null) {
        exit(1);
    }
    file_put_contents(__DIR__.'/feedbacks/f'.time().'.json', $jsonEnc);
    exit(0);
?>