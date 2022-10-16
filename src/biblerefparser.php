<?php

/**
 * Parse single string reference to elementary parts
 * 
 * Return associative array, where key is the original reference (parameter $ref)
 * and value is an array of elementarized arrays.
 * 
 * Elementarized array has three keys - book (string), chap (string) and ver, which
 * is either string (single verse) or array (verse range from-to)
 * 
 * @param string $ref One reference
 * 
 * @return array
 * 
 * @todo Need to be able to parse 3:14-4:2, that needs a change of the returned array scheme
 */
function parseRef(string $ref): array
{
    //Check that book reference and the rest of the reference was found
    if (preg_match('/^([\p{L}0-9]+)\s+(.*)$/u', $ref, $mat) !== 1 || count($mat) < 3) {
        return [];
    }

    //Save basic reference parts for clarity
/*    $tmp = [
        'book' => $mat[1],
        'ref' => [],
        'text' => ''
    ];*/
    $tmp = [$mat[1], 0, 0, ''];
    $chap = '';
  
    //Split reference into single verses or verse ranges
    $rngArr = [];
    $rngTok = strtok($mat[2], ',+');
    while ($rngTok !== false) {
        $rngArr[] = $rngTok;
        $rngTok = strtok(',+');
    }

    //Unification of all single verse/verse range refs, so that
    //they all have book and chapter
    $rng = [];
    foreach ($rngArr as $rngOne) {
        if (preg_match('/(([0-9]+):)?(\d+)(-)?(([0-9]+):)?(\d+)?/', $rngOne, $mat2) === 1/* && count($mat2) == 4*/) {
            if (count($mat2) == 4) {
                if ($mat2[2] != '') {
                    $chap = $mat2[2];
                }
                $tmp[1] = chapVer($chap, $mat2[3]);
                $tmp[2] = $tmp[1];
            } elseif (count($mat2) == 8) {
                if ($mat2[2] != '') {
                    $chap = $mat2[2];
                }
                $tmp[1] = chapVer($chap, $mat2[3]);
                if ($mat2[6] != '') {
                    $chap = $mat2[6];
                }
                $tmp[2] = chapVer($chap, $mat2[7]);
            }
            $rng[] = $tmp;
/*            if ($mat2[2] != '') {
                $tmp['chap'] = trim($mat2[2]);
            }
            $ver = trim($mat2[3]);
            if (strpos($ver, '-')) {
                $ver = explode('-', $ver);
            }
            $tmp['ver'] = $ver;
            $rng[] = $tmp;*/
        }
    }

    return $rng;
    /*
    $rng = [
        "Ps 29:3b+9b-10" => [
            [
                "book" => "Ps",
                "chap" => "29",
                "ver" => "3b",
                "text => ""
            ],
            [
                "book" => "Ps",
                "chap" => "29",
                "ver" => ["9b", "10"],
                "text => ""
            ]
        ]
    ];   
    */
}

/**
 * Function that allows for parsing either string reference or array of string 
 * references
 * 
 * @param string|string[] $refs Reference(s)
 * 
 * @return array
 * 
 * @see parseRef()
 */
function parseRefs($refs): array
{
    if (is_string($refs)) {
        return [$refs => parseRef($refs)];
    } elseif (is_array($refs)) {
        $arr = [];
        foreach ($refs as $oneref) {
            $arr[$oneref] = parseRef($oneref);
        }
        return $arr;
    }
}

/**
 * 
 */
function chapVer(int $chap, int $ver): int
{
    return (int)sprintf('%d%04d', $chap, $ver);
}
