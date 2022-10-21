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

if (defined('OOM_BASE') === false) {
    die('This file cannot be viewed independently.');
}

/**
 * Hello
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */
class CommonBible
{

    /**
     * Path to the Bible XML
     *
     * @var string $bibFile
     */
    private $bibFile;

    /**
     * Parsed verse references
     *
     * @var array $parsedRef
     */
    private $parsedRef;

    /**
     * Current book and chapter
     *
     * @var array $current
     */
    private $current;

    /**
     * Type of XML (Zefania, USFX, OSIS)
     *
     * @var integer $type
     */
    private $type;

    /**
     * Reference to the variable, where to store correct verse text
     *
     * @var string $_textRef
     */


    /**
     * Hello
     */
    public function __construct()
    {
        $this->bibFile   = '';
        $this->parsedRef = [];
        $this->current   = [
            'book' => '',
            'chap' => '',
        ];
        $this->type      = -1;

    }//end __construct()


    /**
     * Hello
     *
     * @param string $file Hello
     *
     * @return void
     */
    public function defineFile(string $file)
    {
        $this->bibFile = $file;
        if (preg_match('/zefania.xml$/', $this->bibFile) === 1) {
            $this->type = 0;
        } else if (preg_match('/usfx.xml$/', $this->bibFile) === 1) {
            $this->type = 1;
        } else if (preg_match('/osis.xml$/', $this->bibFile) === 1) {
            $this->type = 2;
        }

    }//end defineFile()


    /**
     * Hello
     *
     * @param XMLParser|resource $parser  Hello
     * @param string             $name    Hello
     * @param array              $attribs Hello
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
            $vers        = $attribs['VNUMBER'];
            $currChapver = MassHelper::chapVer($this->current['chap'], $vers);
            foreach ($this->parsedRef as $refRaw => &$refElems) {
                foreach ($refElems as &$refElem) {
                    if (strcasecmp($refElem[0], $this->current['book']) === 0) {
                        if ($currChapver >= $refElem[1] && $currChapver <= $refElem[2]) {
                            $this->_textRef = &$refElem[3];
                        }
                    }
                }
            }
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
            $vers = $attribs['ID'];
            $b    = true;
            try {
                $currChapver = MassHelper::chapVer($this->current['chap'], $vers);
            } catch (\Throwable $th) {
                $b = false;
            }

            if ($b === true) {
                foreach ($this->parsedRef as $refRaw => &$refElems) {
                    foreach ($refElems as &$refElem) {
                        if (strcasecmp($refElem[0], $this->current['book']) === 0) {
                            if ($currChapver >= $refElem[1] && $currChapver <= $refElem[2]) {
                                $this->_textRef = &$refElem[3];
                            }
                        }
                    }
                }
            }
        } else if ($name === 'VE' && isset($this->_textRef) === true) {
            $this->_textRef .= ' ';
            unset($this->_textRef);
            // = null;
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
            $vers        = $attribs['N'];
            $currChapver = MassHelper::chapVer($this->current['chap'], $vers);
            foreach ($this->parsedRef as $refRaw => &$refElems) {
                foreach ($refElems as &$refElem) {
                    if (strcasecmp($refElem[0], $this->current['book']) === 0) {
                        if ($currChapver >= $refElem[1] && $currChapver <= $refElem[2]) {
                            $this->_textRef = &$refElem[3];
                        }
                    }
                }
            }
        } else if ($name === 'VERSE' && array_key_exists('EID', $attribs) === true && isset($this->_textRef) === true) {
            $this->_textRef .= ' ';
            unset($this->_textRef);
        }

    }//end startHdl2()


    /**
     * Hello
     *
     * @param XMLParser $parser Hello
     * @param string    $name   Hello
     *
     * @return void
     */
    private function endHdl0($parser, string $name)
    {
        if ($name === 'BIBLEBOOK') {
            $this->current['book'] = '';
        } else if ($name === 'CHAPTER') {
            $this->current['chap'] = '';
        } else if ($name === 'VERS' && isset($this->_textRef) === true) {
            $this->_textRef .= ' ';
            unset($this->_textRef);
            // = null;
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
     * @param XMLParser $parser Hello
     * @param string    $data   Hello
     *
     * @return void
     */
    private function midHdl($parser, string $data)
    {
        if (isset($this->_textRef) === true) {
            $this->_textRef .= $data;
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
        if (file_exists($this->bibFile) === false) {
            return '';
        }

        $this->parsedRef = MassHelper::parseRefs($ref);

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

        while (($data = fread($stream, 16384)) !== false) {
            xml_parse($parser, $data);
        }

        xml_parse($parser, '', true);
        xml_parser_free($parser);
        fclose($stream);
        unset($data);

        $text = '';
        foreach ($this->parsedRef as $refK => $refV) {
            foreach ($refV as $refA) {
                $text .= $refA[3];
            }
        }

        return trim($text);

    }//end getByRef()


}//end class
