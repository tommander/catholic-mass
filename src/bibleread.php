<?php
/**
 * Bible reader
 * 
 * PHP version 7.4
 * 
 * @category FileReader
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */

if (!defined('OOM_BASE')) {
    die('This file cannot be viewed independently.');
}

require __DIR__.'/biblerefparser.php';

/**
 * Hello
 *
 * @category FileReader
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */
class CommonBible
{

    /**
     * Path to the Bible XML
     * 
     * @var string $_bibFile
     */
    private $_bibFile;

    /**
     * Parsed verse references
     * 
     * @var array $_parsedRef
     */
    private $_parsedRef;

    /**
     * Current book and chapter
     * 
     * @var array $_current
     */
    private $_current;

    /**
     * Type of XML (Zefania, USFX, OSIS)
     * 
     * @var int $_type
     */
    private $_type;

    /**
     * Reference to the variable, where to store correct verse text
     * 
     * @var string $_textRef
     */
    //private $_textRef;

    /**
     * Hello
     * 
     * @param string $file Comment
     */
    function __construct(string $file)
    {
        $this->_bibFile = $file;
        $this->_parsedRef = [];
        $this->_current = [
            'book' => '',
            'chap' => ''
        ];
        if (preg_match('/zefania.xml$/', $this->_bibFile)) {
            $this->_type = 0;
        } elseif (preg_match('/usfx.xml$/', $this->_bibFile)) {
            $this->_type = 1;
        } elseif (preg_match('/osis.xml$/', $this->_bibFile)) {
            $this->_type = 2;
        }

    }

    /**
     * Hello
     * 
     * @param XMLParser|resource $parser  Hello
     * @param string    $name    Hello
     * @param array     $attribs Hello
     * 
     * @return [type]
     */
    private function _startHdl0($parser, string $name, array $attribs)
    {
        if ($name == 'BIBLEBOOK' && array_key_exists('BSNAME', $attribs)) {
            $this->_current['book'] = $attribs['BSNAME'];
        } elseif ($name == 'CHAPTER' && array_key_exists('CNUMBER', $attribs)) {
            $this->_current['chap'] = $attribs['CNUMBER'];
        } elseif ($name == 'VERS' && array_key_exists('VNUMBER', $attribs)) {
            $vers = $attribs['VNUMBER'];
            $currChapver = chapVer($this->_current['chap'], $vers);
            foreach ($this->_parsedRef as $refRaw=>&$refElems) {
                foreach ($refElems as &$refElem) {
                    if (strcasecmp($refElem[0], $this->_current['book']) == 0) {
                        if ($currChapver >= $refElem[1] && $currChapver <= $refElem[2]) {
                            $this->_textRef = &$refElem[3];
                        }
                    }
                }
            }
        }
    }

    private function _startHdl1($parser, string $name, array $attribs)
    {
        if ($name == 'BOOK' && array_key_exists('ID', $attribs)) {
            $this->_current['book'] = $attribs['ID'];
        } elseif ($name == 'C' && array_key_exists('ID', $attribs)) {
            $this->_current['chap'] = $attribs['ID'];
        } elseif ($name == 'V' && array_key_exists('ID', $attribs)) {
            $vers = $attribs['ID'];
            $b = true;
            try {
                $currChapver = chapVer($this->_current['chap'], $vers);
            } catch (\Throwable $th) {
                $b = false;
            }
            if ($b) {
                foreach ($this->_parsedRef as $refRaw=>&$refElems) {
                    foreach ($refElems as &$refElem) {
                        if (strcasecmp($refElem[0], $this->_current['book']) == 0) {
                            if ($currChapver >= $refElem[1] && $currChapver <= $refElem[2]) {
                                $this->_textRef = &$refElem[3];
                            }
                        }
                    }
                }
            }
        } elseif ($name == 'VE' && isset($this->_textRef)) {
            $this->_textRef .= ' ';
            unset($this->_textRef);// = null;
        }
    }

    private function _startHdl2($parser, string $name, array $attribs)
    {
        if ($name == 'DIV'
            && array_key_exists('TYPE', $attribs)
            && $attribs['TYPE'] == 'book'
            && array_key_exists('OSISID', $attribs)
        ) {
            $this->_current['book'] = $attribs['OSISID'];
        } elseif ($name == 'CHAPTER' && array_key_exists('N', $attribs)) {
            $this->_current['chap'] = $attribs['N'];
        } elseif ($name == 'VERSE' && array_key_exists('N', $attribs)) {
            $vers = $attribs['N'];
            $currChapver = chapVer($this->_current['chap'], $vers);
            foreach ($this->_parsedRef as $refRaw=>&$refElems) {
                foreach ($refElems as &$refElem) {
                    if (strcasecmp($refElem[0], $this->_current['book']) == 0) {
                        if ($currChapver >= $refElem[1] && $currChapver <= $refElem[2]) {
                            $this->_textRef = &$refElem[3];
                        }
                    }
                }
            }
        } elseif ($name == 'VERSE' && array_key_exists('EID', $attribs) && isset($this->_textRef)) {
            $this->_textRef .= ' ';
            unset($this->_textRef);
        }
    }


    /**
     * Hello
     * 
     * @param XMLParser $parser Hello
     * @param string    $name   Hello
     * 
     * @return [type]
     */
    private function _endHdl0($parser, string $name)
    {
        if ($name == 'BIBLEBOOK') {
            $this->_current['book'] = '';
        } elseif ($name == 'CHAPTER') {
            $this->_current['chap'] = '';
        } elseif ($name == 'VERS' && isset($this->_textRef)) {
            $this->_textRef .= ' ';
            unset($this->_textRef);// = null;
        }
    }

    private function _endHdl12($parser, string $name)
    {
    }

    /**
     * Hello
     * 
     * @param XMLParser $parser Hello
     * @param string    $data   Hello
     * 
     * @return [type]
     */
    private function _midHdl($parser, string $data)
    {
        if (isset($this->_textRef)) {
            $this->_textRef .= $data;
        }
    }

    /**
     * Short description
     * 
     * @param string|string[] $ref Reference
     * 
     * @return string Text of the reference
     * 
     * @access public
     */
    function getByRef($ref)
    {
        if (!file_exists($this->_bibFile)) {
            return '';
        }

        $this->_parsedRef = parseRefs($ref);
        
        $stream = fopen($this->_bibFile, 'r');
        $parser = xml_parser_create();

        switch($this->_type) {
        case 0:
            xml_set_element_handler(
                $parser, 
                array($this, '_startHdl0'),
                array($this, '_endHdl0')
            );
            break;
        case 1:
            xml_set_element_handler(
                $parser, 
                array($this, '_startHdl1'),
                array($this, '_endHdl12')
            );
            break;
        case 2:
            xml_set_element_handler(
                $parser, 
                array($this, '_startHdl2'),
                array($this, '_endHdl12')
            );
            break;
        }
        xml_set_default_handler($parser, array($this, '_midHdl'));

        while (($data = fread($stream, 16384))) {
            xml_parse($parser, $data);
        }

        xml_parse($parser, '', true);
        xml_parser_free($parser);
        fclose($stream);
        unset($data);

        $text = '';
        foreach ($this->_parsedRef as $refK=>$refV) {
            foreach ($refV as $refA) {
                $text .= $refA[3];
            }
        }

        return trim($text);
    }
}
