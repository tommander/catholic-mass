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
class CommonBible
{

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
     * Hello
     */
    public function __construct()
    {
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
     * Start collecting text
     *
     * @return void
     */
    private function startCollecting()
    {
        $currChapver = MassHelper::chapVer(intval($this->current['chap']), intval($this->current['vers']));
        foreach ($this->parsedRef as $refRaw) {
            $this->doCollect = strcasecmp($refRaw[0], $this->current['book']) === 0
                && $currChapver >= intval($refRaw[1])
                && $currChapver <= intval($refRaw[2]);
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

        $this->parsedRef = MassHelper::parseRefs($ref);
        $this->textOut   = '';

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
