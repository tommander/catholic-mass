<?php
/**
 * Bible indexer
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
class BibleIndexer
{

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * Current book and chapter for indexing
     *
     * @var array
     */
    private $currentIndex = [
        'book' => '',
        'chap' => '',
        'vers' => '',
    ];

    /**
     * For indexing purposes
     *
     * @var array
     */
    private $index = [];

    /**
     * Current verse in index
     *
     * @var integer
     */
    private $currentIndexVers = 0;


    /**
     * Hello
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

    }//end __construct()


    /**
     * Adds a new item to the index
     *
     * @param int $pos Position in the file
     *
     * @return void
     */
    private function beginIndex(int $pos)
    {
        $this->currentIndexVers = Helper::chapVer(intval($this->currentIndex['chap']), intval($this->currentIndex['vers']));
        $this->index[$this->currentIndex['book']][$this->currentIndexVers] = [$pos];

    }//end beginIndex()


    /**
     * Finishes index item
     *
     * @param int $pos Position in the file
     *
     * @return void
     */
    private function endIndex(int $pos)
    {
        $this->index[$this->currentIndex['book']][$this->currentIndexVers][] = $pos;

    }//end endIndex()


    /**
     * Start tag handler for Zefania XML parser
     *
     * @param \XMLParser $parser  Parser
     * @param string     $name    Tag name
     * @param array      $attribs Tag attributes
     *
     * @return void
     */
    private function startHandlerIndex0(\XMLParser $parser, string $name, array $attribs)
    {
        if ($name === 'BIBLEBOOK' && array_key_exists('BSNAME', $attribs) === true) {
            $this->currentIndex['book'] = $attribs['BSNAME'];
        } else if ($name === 'CHAPTER' && array_key_exists('CNUMBER', $attribs) === true) {
            $this->currentIndex['chap'] = $attribs['CNUMBER'];
        } else if ($name === 'VERS' && array_key_exists('VNUMBER', $attribs) === true) {
            $this->currentIndex['vers'] = $attribs['VNUMBER'];
            $this->beginIndex(xml_get_current_byte_index($parser));
        }

    }//end startHandlerIndex0()


    /**
     * Start tag handler for USFX XML parser
     *
     * @param \XMLParser $parser  Parser
     * @param string     $name    Tag name
     * @param array      $attribs Tag attributes
     *
     * @return void
     */
    private function startHandlerIndex1(\XMLParser $parser, string $name, array $attribs)
    {
        if ($name === 'BOOK' && array_key_exists('ID', $attribs) === true) {
            $this->currentIndex['book'] = $attribs['ID'];
        } else if ($name === 'C' && array_key_exists('ID', $attribs) === true) {
            $this->currentIndex['chap'] = $attribs['ID'];
        } else if ($name === 'V' && array_key_exists('ID', $attribs) === true) {
            $this->currentIndex['vers'] = $attribs['ID'];
            $this->beginIndex(xml_get_current_byte_index($parser));
        } else if ($name === 'VE') {
            $this->endIndex(xml_get_current_byte_index($parser));
        }//end if

    }//end startHandlerIndex1()


    /**
     * Start tag handler for OSIS XML parser
     *
     * @param \XMLParser $parser  Parser
     * @param string     $name    Tag name
     * @param array      $attribs Tag attributes
     *
     * @return void
     */
    private function startHandlerIndex2(\XMLParser $parser, string $name, array $attribs)
    {
        if ($name === 'DIV'
            && array_key_exists('TYPE', $attribs) === true
            && $attribs['TYPE'] === 'book'
            && array_key_exists('OSISID', $attribs) === true
        ) {
            $this->currentIndex['book'] = $attribs['OSISID'];
        } else if ($name === 'CHAPTER' && array_key_exists('N', $attribs) === true) {
            $this->currentIndex['chap'] = $attribs['N'];
        } else if ($name === 'VERSE' && array_key_exists('N', $attribs) === true) {
            $this->currentIndex['vers'] = $attribs['N'];
            $this->beginIndex(xml_get_current_byte_index($parser));
        } else if ($name === 'VERSE' && array_key_exists('EID', $attribs) === true) {
            $this->endIndex(xml_get_current_byte_index($parser));
        }

    }//end startHandlerIndex2()


    /**
     * End tag handler for Zefania XML parser
     *
     * @param \XMLParser $parser Parser
     * @param string     $name   Tag name
     *
     * @return void
     */
    private function endHandlerIndex0(\XMLParser $parser, string $name)
    {
        if ($name === 'VERS') {
            $this->endIndex(xml_get_current_byte_index($parser));
        }

    }//end endHandlerIndex0()


    /**
     * End tag handler for USFX/OSIS XML parser
     *
     * That's right. It is empty.
     *
     * @param \XMLParser $parser Parser
     * @param string     $name   Tag name
     *
     * @return void
     */
    private function endHandlerIndex12(\XMLParser $parser, string $name)
    {

    }//end endHandlerIndex12()


    /**
     * Create Bible file index
     *
     * @param string $file File
     *
     * @return bool
     */
    public function createIndex(string $file)
    {
        $this->index        = [];
        $this->currentIndex = [
            'book' => '',
            'chap' => '',
            'vers' => '',
        ];

        $fullFile  = __DIR__.'/../libs/open-bibles/'.$file;
        $fullIndex = $fullFile.'.json';

        if (file_exists($fullFile) !== true) {
            return false;
        }

        $md5 = md5_file($fullFile);

        // If the index exists, check the md5 of XML file, whether we need reindexing.
        if (file_exists($fullIndex) === true) {
            $indexFile = Helper::loadJson($fullIndex);
            if (array_key_exists('md5', $indexFile) === true
                && $md5 === $indexFile['md5']
            ) {
                return true;
            }

            unset($indexFile);
        }

        $stream = fopen($fullFile, 'r');
        if ($stream === false) {
            return false;
        }

        try {
            $parser = xml_parser_create();
            try {
                $type = -1;
                if (preg_match('/zefania.xml$/', $file) === 1) {
                    $type = 0;
                } else if (preg_match('/usfx.xml$/', $file) === 1) {
                    $type = 1;
                } else if (preg_match('/osis.xml$/', $file) === 1) {
                    $type = 2;
                }

                if ($type === -1) {
                    throw new \Exception('Invalid Bible XML type');
                }

                switch ($type) {
                case 0:
                    xml_set_element_handler($parser, [$this, 'startHandlerIndex0'], [$this, 'endHandlerIndex0']);
                    break;
                case 1:
                    xml_set_element_handler($parser, [$this, 'startHandlerIndex1'], [$this, 'endHandlerIndex12']);
                    break;
                case 2:
                    xml_set_element_handler($parser, [$this, 'startHandlerIndex2'], [$this, 'endHandlerIndex12']);
                    break;
                }

                while (feof($stream) !== true) {
                    $data = fread($stream, 16384);
                    xml_parse($parser, $data);
                }

                xml_parse($parser, '', true);
                unset($data);
            } finally {
                xml_parser_free($parser);
            }//end try
        } finally {
            fclose($stream);
            $stream = null;
        }//end try

        file_put_contents($fullIndex, json_encode(['md5' => $md5, 'index' => $this->index]));
        $this->index        = [];
        $this->currentIndex = [
            'book' => '',
            'chap' => '',
            'vers' => '',
        ];
        return true;

    }//end createIndex()


}//end class
