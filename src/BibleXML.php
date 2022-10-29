<?php
/**
 * BibleXML unit
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\OrderOfMass;

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Reader for [open-bibles](https://github.com/seven1m/open-bibles) XML files.
 */
class BibleXML
{

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Name of the Bible XML file
     *
     * @var string
     */
    private $bibFile = '';

    /**
     * Parsed verse references
     *
     * @var array
     */
    private $parsedRef = [];

    /**
     * Current book and chapter
     *
     * @var array
     */
    private $current = [
        'book' => '',
        'chap' => '',
        'vers' => '',
    ];

    /**
     * The type of current XML file
     *
     * Types:
     * - `-1` = unknown
     * - `0` = Zefania
     * - `1` = USFX
     * - `2` = OSIS
     *
     * @var integer
     */
    private $type = -1;

    /**
     * Flag that tells the XML parser whether to collect the current text data or not.
     *
     * This flag is set by start/end handlers, which check the start of every verse, whether it is one of the verses that is being looked for.
     *
     * @var boolean
     */
    private $doCollect = false;

    /**
     * Temporary storage of collected text.
     *
     * @var string
     */
    private $textOut = '';

    /**
     * This gives an information whether an index file for the chosen Bible XML exists.
     *
     * @var boolean
     */
    private $isIndex = false;

    /**
     * List of available Bible translations (from biblist.json)
     *
     * The array looks like this (numbers are explained below):
     *
     * ```
     * [
     *   '1' => [
     *     '2' => [
     *       '3',
     *       '4',
     *       [
     *         '5' => '6'
     *       ]
     *     ]
     *   ]
     * ]
     * ```
     *
     * 1. Language code
     * 2. Bible code
     * 3. Bible translation name
     * 4. Bible translation file
     * 5. Common Bible book abbreviation
     * 6. Bible book abbreviation as used in that translation's XML file
     *
     * @var array<string, array<string|array<string, string>>>
     */
    public $biblist = [];

    /**
     * GetParams instance
     *
     * @var GetParams
     */
    private $getParams;

    /**
     * Language instance
     *
     * @var Language
     */
    private $language;

    /**
     * List of Bible book abbreviations (mapping from the common abbreviations used e.g. in `lectlist.json` to abbreviations specific to the particular XML file that has been loaded).
     *
     * @var array
     */
    private $biblistabbr = [];


    /**
     * Saves the instance of Logger
     *
     * @param Logger    $logger    Logger
     * @param GetParams $getParams Hello
     * @param Language  $language  Hello
     */
    public function __construct(Logger $logger, GetParams $getParams, Language $language)
    {
        $this->logger    = $logger;
        $this->getParams = $getParams;
        $this->language  = $language;
        $this->biblist   = Helper::loadJson('assets/json/biblist.json');

        $bible = $this->getParams->getBible();
        foreach ($this->biblist as $biblang => $biblist) {
            foreach ($biblist as $bibid => $bibdata) {
                if ($bibid === $bible) {
                    $this->biblistabbr = $bibdata[2];
                    $this->type        = $this->getFileType($bibdata[1]);
                    $this->bibFile     = 'libs/open-bibles/'.$bibdata[1];
                    $this->isIndex     = file_exists(Helper::fullFilename($this->bibFile.'.json'));
                    break;
                }
            }
        }

    }//end __construct()


    /**
     * Returns a list of Bible XML files.
     *
     * This list is in the form that {@see HtmlMaker::comboBoxContent()} is using to create a `select` node with `optgroup` and `option` nodes inside.
     *
     * @return array
     */
    public function getBibleComboList()
    {
        $ret = [
            '' => [
                [
                    'value' => '',
                    'sel'   => false,
                    'text'  => '-',
                ],
            ],
        ];
        if (array_key_exists($this->getParams->getLabelLang(), $this->biblist) === true) {
            $grplabel = $this->language->getLanguageData($this->getParams->getLabelLang(), 'title');
            if ($grplabel === null) {
                $grplabel = '';
            }

            $ret[$grplabel] = [];
            foreach ($this->biblist[$this->getParams->getLabelLang()] as $bibleid => $bibledata) {
                if (isset($bibledata[0]) !== true) {
                    continue;
                }

                $ret[$grplabel][] = [
                    'value' => $bibleid,
                    'sel'   => $this->getParams->getBible() == $bibleid,
                    'text'  => $bibledata[0],
                ];
            }
        }//end if

        if (array_key_exists($this->getParams->getContentLang(), $this->biblist) === true && $this->getParams->getLabelLang() !== $this->getParams->getContentLang()) {
            $grplabel = $this->language->getLanguageData($this->getParams->getContentLang(), 'title');
            if ($grplabel === null) {
                $grplabel = '';
            }

            $ret[$grplabel] = [];
            foreach ($this->biblist[$this->getParams->getContentLang()] as $bibleid => $bibledata) {
                if (isset($bibledata[0]) !== true) {
                    continue;
                }

                $ret[$grplabel][] = [
                    'value' => $bibleid,
                    'sel'   => $this->getParams->getBible() == $bibleid,
                    'text'  => $bibledata[0],
                ];
            }
        }//end if

        return $ret;

    }//end getBibleComboList()


    /**
     * Return Bible XML file type based on the end of its name
     *
     * @param string $file File name (no need for path)
     *
     * @return int
     *
     * @see BibleXML::$type
     */
    private function getFileType(string $file): int
    {
        if (preg_match('/zefania.xml$/', $file) === 1) {
            return 0;
        }

        if (preg_match('/usfx.xml$/', $file) === 1) {
            return 1;
        }

        if (preg_match('/osis.xml$/', $file) === 1) {
            return 2;
        }

        return -1;

    }//end getFileType()


    /**
     * Check the current book/chapter/verse against the elementary verse references and if that verse's text is needed, raise the collection flag.
     *
     * @return void
     */
    private function startCollecting()
    {
        $currChapver = Helper::chapVer(intval($this->current['chap']), intval($this->current['vers']));
        foreach ($this->parsedRef as $refRaw) {
            if (strcasecmp($refRaw[0], $this->current['book']) === 0
                && $currChapver >= intval($refRaw[1])
                && $currChapver <= intval($refRaw[2])
            ) {
                $this->doCollect = true;
                break;
            }
        }

    }//end startCollecting()


    /**
     * Hang the collection flag, if it has not been done already.
     *
     * @return void
     */
    private function stopCollecting()
    {
        if ($this->doCollect === false) {
            return;
        }

        $this->textOut  .= ' ';
        $this->doCollect = false;

    }//end stopCollecting()


    /**
     * Start tag handler for Zefania XML files.
     *
     * @param \XMLParser|resource $parser  XML parser object/resource.
     * @param string              $name    Name of the tag (uppercase).
     * @param array               $attribs Associative array of the node attributes.
     *
     * @return void
     */
    private function startHdl0($parser, string $name, array $attribs)
    {
        if ($name === 'BIBLEBOOK' && array_key_exists('BSNAME', $attribs) === true) {
            $this->current['book'] = $attribs['BSNAME'];
        } else if ($name === 'CHAPTER' && array_key_exists('CNUMBER', $attribs) === true) {
            $this->current['chap'] = $attribs['CNUMBER'];
        } else if ($name === 'VERS' && array_key_exists('VNUMBER', $attribs) === true) {
            $this->current['vers'] = $attribs['VNUMBER'];
            $this->startCollecting();
        }

    }//end startHdl0()


    /**
     * Start tag handler for USFX XML files.
     *
     * @param \XMLParser|resource $parser  XML parser object/resource.
     * @param string              $name    Name of the tag (uppercase).
     * @param array               $attribs Associative array of the node attributes.
     *
     * @return void
     */
    private function startHdl1($parser, string $name, array $attribs)
    {
        if ($name === 'BOOK' && array_key_exists('ID', $attribs) === true) {
            $this->current['book'] = $attribs['ID'];
        } else if ($name === 'C' && array_key_exists('ID', $attribs) === true) {
            $this->current['chap'] = $attribs['ID'];
        } else if ($name === 'V' && array_key_exists('ID', $attribs) === true) {
            $this->current['vers'] = $attribs['ID'];
            $this->startCollecting();
        } else if ($name === 'VE') {
            $this->stopCollecting();
        }//end if

    }//end startHdl1()


    /**
     * Start tag handler for OSIS XML files.
     *
     * @param \XMLParser|resource $parser  XML parser object/resource.
     * @param string              $name    Name of the tag (uppercase).
     * @param array               $attribs Associative array of the node attributes.
     *
     * @return void
     */
    private function startHdl2($parser, string $name, array $attribs)
    {
        if ($name === 'DIV'
            && array_key_exists('TYPE', $attribs) === true
            && $attribs['TYPE'] === 'book'
            && array_key_exists('OSISID', $attribs) === true
        ) {
            $this->current['book'] = $attribs['OSISID'];
        } else if ($name === 'CHAPTER' && array_key_exists('N', $attribs) === true) {
            $this->current['chap'] = $attribs['N'];
        } else if ($name === 'VERSE' && array_key_exists('N', $attribs) === true) {
            $this->current['vers'] = $attribs['N'];
            $this->startCollecting();
        } else if ($name === 'VERSE' && array_key_exists('EID', $attribs) === true) {
            $this->stopCollecting();
        }

    }//end startHdl2()


    /**
     * End tag handler for Zefania XML files.
     *
     * @param \XMLParser|resource $parser XML parser object/resource.
     * @param string              $name   Name of the tag (uppercase).
     *
     * @return void
     */
    private function endHdl0($parser, string $name)
    {
        if ($name === 'BIBLEBOOK') {
            $this->current['book'] = '';
        } else if ($name === 'CHAPTER') {
            $this->current['chap'] = '';
        } else if ($name === 'VERS') {
            $this->stopCollecting();
        }

    }//end endHdl0()


    /**
     * End tag handler for USFX/OSIS XML files.
     *
     * It is empty. On purpose. It wants to be that way.
     *
     * @param \XMLParser|resource $parser XML parser object/resource.
     * @param string              $name   Name of the tag (uppercase).
     *
     * @return void
     */
    private function endHdl12($parser, string $name)
    {

    }//end endHdl12()


    /**
     * Node data handler for the text between start and end tag (except CDATA).
     *
     * @param \XMLParser $parser XML parser object/resource.
     * @param string     $data   Data between tags.
     *
     * @return void
     */
    private function midHdl($parser, string $data)
    {
        if ($this->doCollect === true) {
            $this->textOut .= $data;
        }

    }//end midHdl()


    /**
     * Parse single string reference to elementary parts.
     *
     * This is important for the XML parser to be able to follow any possible verse reference.
     *
     * It is important to note that the input reference may only contain one book ref.
     *
     * Parsing `Ps 1:2-3, 4b-2:4+5` would give this result:
     *
     * ```
     * array(
     *     ['Ps', 1000200, 1000300],
     *     ['Ps', 1000202, 2000400],
     *     ['Ps', 2000500, 2000500]
     * )
     * ```
     *
     * @param string $ref One reference
     *
     * @return array
     */
    public function parseRef(string $ref): array
    {
        // Check that book reference and the rest of the reference was found.
        if (preg_match('/^([\p{L}0-9]+)\s+(.*)$/u', $ref, $mat) !== 1 || count($mat) < 3) {
            return [];
        }

        // Save basic reference parts for clarity.
        $chap = 0;

        // Split reference into single verses or verse ranges.
        $rngArr = [];
        $rngTok = strtok($mat[2], ',+');
        while ($rngTok !== false) {
            $rngArr[] = $rngTok;
            $rngTok   = strtok(',+');
        }

        // Unification of all single verse/verse range refs, so that
        // they all have book and chapter.
        $rng = [];
        foreach ($rngArr as $rngOne) {
            if (preg_match('/(([0-9]+):)?([0-9A-z]+)(-)?(([0-9]+):)?([0-9A-z]+)?/', $rngOne, $mat2) === 1) {
                $verseLow  = '';
                $verseHigh = '';

                if (count($mat2) === 4) {
                    if ($mat2[2] !== '') {
                        $chap = intval($mat2[2]);
                    }

                    if (preg_match('/(\d+)([A-z]{1})/', $mat2[3], $mat3) === 1) {
                        $verseLow = Helper::chapVer($chap, intval($mat3[1]), Helper::letterToInt($mat3[2]));
                    } else {
                        $verseLow = Helper::chapVer($chap, intval($mat2[3]));
                    }

                    $verseHigh = $verseLow;
                } else if (count($mat2) === 8) {
                    if ($mat2[2] !== '') {
                        $chap = intval($mat2[2]);
                    }

                    if (preg_match('/(\d+)([A-z]{1})/', $mat2[3], $mat4) === 1) {
                        $verseLow = Helper::chapVer($chap, intval($mat4[1]), Helper::letterToInt($mat4[2]));
                    } else {
                        $verseLow = Helper::chapVer($chap, intval($mat2[3]));
                    }

                    if ($mat2[6] !== '') {
                        $chap = intval($mat2[6]);
                    }

                    if (preg_match('/(\d+)([A-z]{1})/', $mat2[7], $mat5) === 1) {
                        $verseHigh = Helper::chapVer($chap, intval($mat5[1]), Helper::letterToInt($mat5[2]));
                    } else {
                        $verseHigh = Helper::chapVer($chap, intval($mat2[7]));
                    }
                }//end if

                $rng[] = [
                    $mat[1],
                    $verseLow,
                    $verseHigh,
                ];
            }//end if
        }//end foreach

        return $rng;

    }//end parseRef()


    /**
     * Allows for parsing either a single string reference or an array of string references.
     *
     * Basically it is just a <q>gate</q> to {@see BibleXML::parseRef()}.
     *
     * @param string|string[] $refs Reference(s)
     *
     * @return array
     */
    public function parseRefs($refs): array
    {
        if (is_string($refs) === true) {
            return $this->parseRef($refs);
        }

        $arr = [];
        foreach ($refs as $oneref) {
            $arr = array_merge($arr, $this->parseRef($oneref));
        }

        return $arr;

    }//end parseRefs()


    /**
     * Hello
     *
     * @param string $dirtyText Hello
     *
     * @return string
     */
    private function sanitizeIndexedText(string $dirtyText): string
    {
        $remWithContent   = [
            'title',
            'f',
        ];
        $regexWithContent = sprintf('~<(%s)(?:\s+[^>]*?)?>.*?<\/\1>~s', implode('|', $remWithContent));
        $regexOnlyTag     = '~<\/?(?:[A-z]+)(?:\s+[^>]*?)?>~';
        $regexComments    = '~<!--.*?-->~';

        // Clean tags, whose content will also be dropped.
        $almostCleanText = \preg_replace($regexWithContent, '', $dirtyText);
        if (is_string($almostCleanText) !== true) {
            return $dirtyText;
        }

        // Clean all remaining tags, but keep their content.
        $closeToCleanText = \preg_replace($regexOnlyTag, '', $almostCleanText);
        if (is_string($closeToCleanText) !== true) {
            return $dirtyText;
        }

        // Clean comments.
        $cleanText = \preg_replace($regexComments, '', $closeToCleanText);
        if (is_string($cleanText) !== true) {
            return $dirtyText;
        }

        return $cleanText;

    }//end sanitizeIndexedText()


    /**
     * Given a reference, it returns the text of this reference from the current Bible XML.
     *
     * @param string          $book Hello
     * @param string|string[] $ref  Reference
     *
     * @return string Text of the reference
     */
    public function getByRef($book, $ref)
    {
        if (file_exists($this->bibFile) !== true) {
            return '';
        }

        if (isset($this->biblistabbr[$book]) !== true) {
            return '';
        }

        $bookTrans = $this->biblistabbr[$book];
        if (is_string($ref) === true) {
            $refTrans = $bookTrans.' '.$ref;
        } else {
            $refTrans = [];
            foreach ($ref as $oneRef) {
                $refTrans[] = $bookTrans.' '.$oneRef;
            }
        }

        $this->parsedRef = $this->parseRefs($refTrans);
        $this->textOut   = '';

        if ($this->isIndex === true) {
            $indexJson = Helper::loadJson($this->bibFile.'.json');
            if (array_key_exists('index', $indexJson) !== true) {
                return '';
            }

            $stream = fopen($this->bibFile, 'r');
            try {
                foreach ($this->parsedRef as $parsedOneRef) {
                    $refBegin = \substr($parsedOneRef[1], 0, -2).'00';
                    $refEnd   = \substr($parsedOneRef[2], 0, -2).'00';

                    if (array_key_exists($parsedOneRef[0], $indexJson['index']) !== true
                        || array_key_exists($refBegin, $indexJson['index'][$parsedOneRef[0]]) !== true
                        || array_key_exists($refEnd, $indexJson['index'][$parsedOneRef[0]]) !== true
                    ) {
                        continue;
                    }

                    $readBegin = intval($indexJson['index'][$parsedOneRef[0]][$refBegin][0]);
                    $readEnd   = intval($indexJson['index'][$parsedOneRef[0]][$refEnd][1]);

                    $seekOffset = 2;
                    if ($this->type === 0) {
                        $seekOffset = 1;
                    }

                    if (fseek($stream, ($readBegin + $seekOffset)) === 0) {
                        $data = fread($stream, ($readEnd - $readBegin));
                        if (is_string($data) === true) {
                            $sta1 = substr($parsedOneRef[1], -2);
                            $sta2 = substr($parsedOneRef[2], -2);

                            $data = $this->sanitizeIndexedText($data);

                            if ($sta1 !== '00') {
                                $sta1int = intval($sta1);
                                while ($sta1int > 0) {
                                    $pos = strpos($data, '.');
                                    if ($pos === false) {
                                        break;
                                    }

                                    $data = substr($data, ($pos + 1));
                                    $sta1int--;
                                }
                            }

                            if ($sta2 !== '00') {
                                $sta2int = intval($sta2);
                                while ($sta2int > 0) {
                                    $pos = strpos($data, '.', -1);
                                    if ($pos === false) {
                                        break;
                                    }

                                    $data = substr($data, ($pos + 1));
                                    $sta2int--;
                                }
                            }

                            $this->textOut .= $data;
                        }//end if
                    }//end if
                }//end foreach
            } finally {
                fclose($stream);
            }//end try

            return $this->textOut;
        }//end if

        $stream = fopen($this->bibFile, 'r');
        $parser = xml_parser_create();

        switch ($this->type) {
        case 0:
            xml_set_element_handler(
                $parser,
                [
                    $this,
                    'startHdl0',
                ],
                [
                    $this,
                    'endHdl0',
                ]
            );
            break;
        case 1:
            xml_set_element_handler(
                $parser,
                [
                    $this,
                    'startHdl1',
                ],
                [
                    $this,
                    'endHdl12',
                ]
            );
            break;
        case 2:
            xml_set_element_handler(
                $parser,
                [
                    $this,
                    'startHdl2',
                ],
                [
                    $this,
                    'endHdl12',
                ]
            );
        }//end switch

        xml_set_default_handler($parser, [$this, 'midHdl']);

        while (feof($stream) !== true) {
            $data = fread($stream, 16384);
            xml_parse($parser, $data);
        }

        xml_parse($parser, '', true);
        xml_parser_free($parser);
        fclose($stream);
        unset($data);

        return trim($this->textOut);

    }//end getByRef()


}//end class
