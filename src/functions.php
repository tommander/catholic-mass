<?php

if (!defined('OOM_BASE')) {
	die('This file cannot be viewed independently.');
}

/**
 * This function returns a link to a particular commit that was deployed.

 * The function is quite strict, so if the file does not exist or does not contain
 * precisely 40 hexadecimal characters (lowercase), it returns an empty string.
 *
 * Note that the file "commit" is not in the repository; it makes sense only in a 
 * particular deployment site.
 *
 * @return string HTML link ("a" tag) to a particular deployed commit.
 */
function showCommit()
{
    $commitFileName = __DIR__.'/../commit';
    if (!file_exists($commitFileName)) {
        return '';
    }

    $commit = trim(file_get_contents($commitFileName));
    if (!preg_match('/^[a-f0-9]{40}$/', $commit)) {
        return '';
    }

    return sprintf(' (<a href="https://github.com/tommander/catholic-mass/commit/%s">commit %s</a>)', $commit, substr($commit, 0, 7));
}
