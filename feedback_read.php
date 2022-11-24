<?php
    function decryptIt(string $base64text, string $keyFile = 'latest'): string|null {
        $dir = __DIR__.'/feedbacks/';
        if ($keyFile === 'latest') {
            $indexFile = file_get_contents($dir.'_pub');
            if ($indexFile === false) {
                echo "ERROR: index file for public key \"${indexFile}\" does not exist or cannot be read\r\n";
                return null;
            }    
        } else {
            $indexFile = $keyFile;
        }
        $privkey = file_get_contents($dir.$indexFile.'.key');
        if ($privkey === false) {
            echo "ERROR: private key file \"${privkey}\" does not exist or cannot be read\r\n";
            return null;
        }
        if (openssl_private_decrypt(base64_decode($base64text), $res, $privkey) === true) {
            return $res;
        }
        echo "ERROR: decryption failed\r\n";
        return null;
    }

    $dir = __DIR__.'/feedbacks/';
    if (isset($_GET['file']) !== true) {
        echo "ERROR: unspecified input file\r\n";
        exit(1);
    }
    if (is_string($_GET['file']) !== true) {
        echo "ERROR: input file name is not string\r\n";
        exit(1);
    }
    $jsonFile = $_GET['file'];
    $keyFile = 'latest';
    if (isset($_GET['key']) === true && is_string($_GET['key']) === true) {
        $keyFile = $_GET['key'];
    }
    $json = file_get_contents($dir.$jsonFile);
    if ($json === false) {
        echo "ERROR: input file \"${json}\" does not exist or cannot be read\r\n";
        exit(1);
    }

    $dec = decryptIt($json, $keyFile);
    if ($dec === null) {
        exit(1);
    }

    echo $dec."\r\n";
    exit(0);
?>