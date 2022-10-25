<?php
/**
 * Bible XML reader
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
 * Hello
 */
class BibleXML
{

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * Path to the Bible XML
     *
     * @var string
     */
    private $bibFile;

    /**
     * Parsed verse references
     *
     * @var array
     */
    private $parsedRef;

    /**
     * Current book and chapter
     *
     * @var array
     */
    private $current;

    /**
     * Type of XML (Zefania, USFX, OSIS)
     *
     * @var integer
     */
    private $type;

    /**
     * Whether to collect text
     *
     * @var boolean
     */
    private $doCollect;

    /**
     * Collected text
     *
     * @var string
     */
    private $textOut;

    /**
     * Is index?
     *
     * @var boolean
     */
    private $isIndex = false;


    /**
     * Hello
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger    = $logger;
        $this->bibFile   = '';
        $this->parsedRef = [];
        $this->current   = [
            'book' => '',
            'chap' => '',
            'vers' => '',
        ];
        $this->type      = -1;
        $this->doCollect = false;
        $this->textOut   = '';

    }//end __construct()


    /**
     * Return Bible XML file type based on the end of its name
     *
     * - Unknown = -1
     * - Zefania = 0
     * - USFX = 1
     * - OSIS = 2
     *
     * @param string $file Filename (no need for path)
     *
     * @return int
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
     * Hello
     *
     * @param string $file Hello
     *
     * @return void
     */
    public function defineFile(string $file)
    {
        $this->type    = $this->getFileType($file);
        $this->bibFile = 'libs/open-bibles/'.$file;
        $this->isIndex = file_exists(Helper::fullFilename($this->bibFile.'.json'));

    }//end defineFile()


    /**
     * Start collecting text
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
     * Stop collecting text
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
     * Hello
     *
     * @param \XMLParser|resource $parser  Hello
     * @param string              $name    Hello
     * @param array               $attribs Hello
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
     * Hello
     *
     * @param mixed  $parser  Hello
     * @param string $name    Hello
     * @param array  $attribs Hello
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
     * Hello
     *
     * @param mixed  $parser  Hello
     * @param string $name    Hello
     * @param array  $attribs Hello
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
     * Hello
     *
     * @param \XMLParser $parser Hello
     * @param string     $name   Hello
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
     * Hello
     *
     * @param mixed  $parser Hello
     * @param string $name   Hello
     *
     * @return void
     */
    private function endHdl12($parser, string $name)
    {

    }//end endHdl12()


    /**
     * Hello
     *
     * @param \XMLParser $parser Hello
     * @param string     $data   Hello
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
     * Short description
     *
     * @param string|string[] $ref Reference
     *
     * @return string Text of the reference
     *
     * @access public
     */
    public function getByRef($ref)
    {
        if (file_exists($this->bibFile) !== true) {
            return '';
        }

        $this->parsedRef = Helper::parseRefs($ref);
        $this->textOut   = '';

        if ($this->isIndex === true) {
            $indexJson = Helper::loadJson($this->bibFile.'.json');
            if (array_key_exists('index', $indexJson) !== true) {
                return '';
            }

            $stream = fopen($this->bibFile, 'r');
            try {
                foreach ($this->parsedRef as $ref) {
                    $refBegin = \substr($ref[1], 0, -2).'00';
                    $refEnd   = \substr($ref[2], 0, -2).'00';

                    if (array_key_exists($ref[0], $indexJson['index']) !== true
                        || array_key_exists($refBegin, $indexJson['index'][$ref[0]]) !== true
                        || array_key_exists($refEnd, $indexJson['index'][$ref[0]]) !== true
                    ) {
                        continue;
                    }

                    $readBegin = intval($indexJson['index'][$ref[0]][$refBegin][0]);
                    $readEnd   = intval($indexJson['index'][$ref[0]][$refEnd][1]);

                    $seekOffset = 2;
                    if ($this->type === 0) {
                        $seekOffset = 1;
                    }

                    if (fseek($stream, ($readBegin + $seekOffset)) === 0) {
                        $data = fread($stream, ($readEnd - $readBegin));
                        if (is_string($data) === true) {
                            $sta1 = substr($ref[1], -2);
                            $sta2 = substr($ref[2], -2);

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

            $this->textOut = preg_replace('~<f caller="\+">.*?<\/f>~si', '', $this->textOut);
            $this->textOut = strip_tags($this->textOut);
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
            break;
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
