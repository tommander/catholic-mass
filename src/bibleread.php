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

//if (!defined('OOM_BASE')) {
//    die('This file cannot be viewed independently.');
//}

require 'biblerefparser.php';

/**
 * Hello
 *
 * @category FileReader
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */
/*interface BibleRead
{*/
    
    /**
     * Hello
     * 
     * @param string $file Comment
     */
//    function __construct(string $file);

    /**
     * Short description
     * 
     * @param string $ref Reference
     * 
     * @return string|array Text of the reference
     * 
     * @access public
     */
//    function getByRef($ref);
/* }*/

/**
 * 
 */
class CommonBible
{
    
}

/**
 * Hello
 *
 * @category FileReader
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */
class ZefaniaBible extends CommonBible // implements BibleRead
{
    /**
     * Hello
     * 
     * @var [type]
     */
    private $_bibFile;

    /**
     * Hello
     * 
     * @var [type]
     */
    private $_parsedRef;

    /**
     * Hello
     * 
     * @var [type]
     */
    private $_current;

    /**
     * Hello
     * 
     * @var [type]
     */
    //private $_textRef;

    /**
     * Hello
     * 
     * @var [type]
     */
    public $brlog;

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
        //$this->_textRef = null;
        $this->brlog = '';
    }

    /**
     * Hello
     * 
     * @param XMLParser $parser  Hello
     * @param string    $name    Hello
     * @param array     $attribs Hello
     * 
     * @return [type]
     */
    private function _startHdl(XMLParser $parser, string $name, array $attribs)
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

    /**
     * Hello
     * 
     * @param XMLParser $parser Hello
     * @param string    $name   Hello
     * 
     * @return [type]
     */
    private function _endHdl(XMLParser $parser, string $name)
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

    /**
     * Hello
     * 
     * @param XMLParser $parser Hello
     * @param string    $data   Hello
     * 
     * @return [type]
     */
    private function _midHdl(XMLParser $parser, string $data)
    {
        if (isset($this->_textRef)/* !== null*/) {
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

        xml_set_element_handler(
            $parser, 
            array($this, '_startHdl'),
            array($this, '_endHdl')
        );
        xml_set_default_handler($parser, array($this, '_midHdl'));

        while (($data = fread($stream, 16384))) {
            xml_parse($parser, $data); // parse the current chunk
        }

        xml_parse($parser, '', true); // finalize parsing
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

/**
 * Hello
 *
 * @category FileReader
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */
class UsfxBible extends CommonBible //implements BibleRead
{
    /**
     * Hello
     * 
     * @var [type]
     */
    private $_bibFile;

    /**
     * Hello
     * 
     * @var [type]
     */
    private $_parsedRef;

    /**
     * Hello
     * 
     * @var [type]
     */
    private $_current;

    /**
     * Hello
     * 
     * @var [type]
     */
    //private $_textRef;

    /**
     * Hello
     * 
     * @var [type]
     */
    public $brlog;

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
        //$this->_textRef = null;
        $this->brlog = '';
    }

    /**
     * Hello
     * 
     * @param XMLParser $parser  Hello
     * @param string    $name    Hello
     * @param array     $attribs Hello
     * 
     * @return [type]
     */
    private function _startHdl(XMLParser $parser, string $name, array $attribs)
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

    /**
     * Hello
     * 
     * @param XMLParser $parser Hello
     * @param string    $name   Hello
     * 
     * @return [type]
     */
    private function _endHdl(XMLParser $parser, string $name)
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
    private function _midHdl(XMLParser $parser, string $data)
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

        xml_set_element_handler(
            $parser, 
            array($this, '_startHdl'),
            array($this, '_endHdl')
        );
        xml_set_default_handler($parser, array($this, '_midHdl'));

        while (($data = fread($stream, 16384))) {
            xml_parse($parser, $data); // parse the current chunk
        }

        xml_parse($parser, '', true); // finalize parsing
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

/**
 * Hello
 *
 * @category FileReader
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */
class OsisBible extends CommonBible // implements BibleRead
{
    /**
     * Hello
     * 
     * @var [type]
     */
    private $_bibFile;

    /**
     * Hello
     * 
     * @var [type]
     */
    private $_parsedRef;

    /**
     * Hello
     * 
     * @var [type]
     */
    private $_current;

    /**
     * Hello
     * 
     * @var [type]
     */
    //private $_textRef;

    /**
     * Hello
     * 
     * @var [type]
     */
    public $brlog;

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
        //$this->_textRef = null;
        $this->brlog = '';
    }

    /**
     * Hello
     * 
     * @param XMLParser $parser  Hello
     * @param string    $name    Hello
     * @param array     $attribs Hello
     * 
     * @return [type]
     */
    private function _startHdl(XMLParser $parser, string $name, array $attribs)
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
    private function _endHdl(XMLParser $parser, string $name)
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
    private function _midHdl(XMLParser $parser, string $data)
    {
        if (isset($this->_textRef)/* !== null*/) {
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

        xml_set_element_handler(
            $parser, 
            array($this, '_startHdl'),
            array($this, '_endHdl')
        );
        xml_set_default_handler($parser, array($this, '_midHdl'));

        while (($data = fread($stream, 16384))) {
            xml_parse($parser, $data); // parse the current chunk
        }

        xml_parse($parser, '', true); // finalize parsing
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
