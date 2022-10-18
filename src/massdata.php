<?php
/**
 * Main file for the Order of Mass app
 *
 * PHP version 7.4
 *
 * @category MainClass
 * @package  OrderOfMass
 * @author   Tommander <tommander@tommander.cz>
 * @license  GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     mass.tommander.cz
 */

namespace OrderOfMass;

if (!defined('OOM_BASE')) {
    die('This file cannot be viewed independently.');
}


/**
 * Class that helps to convert language JSON files to HTML
 */
class MassData
{
    /**
     * Instance of CommonBible, which allows for reading from XML-encoded Bible
     * translations.
     *
     * @var CommonBible
     */
    private $_biblexml;

    /**
     * Mass/texts language (from data/langlist.json)
     *
     * This is in the form of an
     * (https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes)[ISO_639-2 code]
     *
     * @var string
     */
    public $tl = 'eng';

    /**
     * Web/labels language (from data/langlist.json)
     *
     * This is in the form of an
     * (https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes)[ISO_639-2 code]
     *
     * @var string
     */
    public $ll = 'eng';

    /**
     * Bible translation ID (from biblist.json)
     *
     * This is used to correctly initialize {@see MassData::$_biblexml}
     *
     * @var string
     */
    public $bl = '';

    /**
     * List of available languages (from data/langlist.json)
     *
     * The array looks like this (numbers are explained below):
     *
     * ```
     * [
     *   '1' => [
     *     'title' => '2',
     *     'author' => '3',
     *     'link' => '4'
     *   ]
     * ]
     * ```
     *
     * 1. Language three-letter code
     * 2. Language name (in that language, i.e. <q>English</q>, <q>Deutsch</q>, ...)
     * 3. Author of that translation (not the language, obviously)
     * 4. Either a URL or simple array of URLs (sources of information)
     *
     * @var array<string, array<string, string|string[]>>
     */
    public $langs = [];

    /**
     * List of label translations (from data/xxx.json)
     *
     * `xxx` stands for the three-letter language code
     *
     * - Array key is a label ID (e.g. <q>holycomm</q>)
     * - Array value is that label's translated full text (e.g. <q>Holy
     *   Communion</q>)
     *
     * @var array<string, string>
     */
    private $_labels = [];

    /**
     * List of Sunday name translations (from data/xxx.json)
     *
     * `xxx` stands for the three-letter language code
     *
     * - Array key is the code of a particular Sunday (e.g. <q>SIOT2</q>)
     * - Array value is the translated full name of that Sunday (e.g. <q>2. Sunday
     *   in Ordinary time</q>)
     *
     * @var array<string, string>
     */
    private $_sundays = [];

    /**
     * List of rosary mystery translations (from data/xxx.json)
     *
     * `xxx` stands for the three-letter language code
     *
     * The array looks like this:
     *
     * ```
     * [
     *   'j' => '', //translation of "Joyful mysteries"
     *   's' => '', //translation of "Sorrowful mysteries"
     *   'g' => '', //translation of "Glorious mysteries"
     *   'l' => '' //translation of "Luminous mysteries"
     * ]
     * ```
     *
     * @var array<string, string>
     */
    private $_mysteries = [];

    /**
     * Closest next Sunday readings
     *
     * The array looks like this:
     *
     * ```
     * [
     *   'r1' => 'Gn 1:1', //first reading
     *   'r2' => 'Ex 1:2-3', //second reading
     *   'p' => 'Ps 1:4', //responsorial psalm
     *   'a' => 'Ps 1:5+6', //alleluia
     *   'g' => 'Mk 1:7' //gospel
     * ]
     * ```
     *
     * @var array<string, string>
     *
     * @see MassReadings::lectio()
     */
    public $reads = [];

    /**
     * Bible books abbreviations and titles (from data/xxx.json)
     *
     * `xxx` stands for the three-letter language code
     *
     * The array looks like this (numbers are explained below):
     *
     * ```
     * [
     *   '1' => '['abbr' => '2', 'title' => '3']'
     * ]
     * ```
     *
     * 1. Common abbreviation as used in the lectionary
     * 2. Translated book abbreviation
     * 3. Translated book title
     *
     * @var array<string, array<string, string>>
     */
    private $_bible = [];

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
    public $bibtrans = [];

    /**
     * List of Bible book abbreviations as used in the currently loaded Bible
     * translation (from biblist.json).
     *
     * - Array key is the common Bible book abbreviation as used in `biblist.json`
     * - Array value is the Bible book abbreviation as used in the Bible translation
     *   XML file
     *
     * @var array<string, string>
     */
    private $_bibtransabbr = [];

    /**
     * List of available Font Awesome icons
     *
     * - Array key is the icon ID as used in the placeholder `@icon{iconID}`
     * - Array value is the CSS class of the `<i>` tag that Font Awesome uses
     *
     * @var array<string, string>
     */
    private $_icons = [
        'cross' => 'fas fa-cross',
        'bible' => 'fas fa-bible',
        'bubble' => 'far fa-comment',
        'peace' => 'far fa-handshake',
        'walk' => 'fas fa-hiking',
        'stand' => 'fas fa-male',
        'sit' => 'fas fa-chair',
        'kneel' => 'fas fa-pray',
        'booklink' => 'fas fa-book-reader',
        'bread' => 'fas fa-cookie-bite',
        'wine' => 'fas fa-wine-glass-alt'
    ];

    /**
     * Class constructor that initializes internal properties
     *
     * Sets {@see MassData::$tl} and {@see MassData::$ll}
     * and then loads content from language files to
     * {@see MassData::$langs} and {@see MassData::$_labels}
     */
    public function __construct()
    {
        $this->langs = $this->_loadJson('data', 'langlist');
        $this->bibtrans = $this->_loadJson('', 'biblist');
        $this->_biblexml = null;

        if (array_key_exists('ll', $_GET)) {
            if (array_key_exists($_GET['ll'], $this->langs) !== false) {
                if (preg_match('/^[a-z]{3}$/', $_GET['ll'])) {
                    $this->ll = $_GET['ll'];
                }
            }
        }

        if (array_key_exists('tl', $_GET)) {
            if (array_key_exists($_GET['tl'], $this->langs) !== false) {
                if (preg_match('/^[a-z]{3}$/', $_GET['tl'])) {
                    $this->tl = $_GET['tl'];
                }
            }
        }

        if (array_key_exists('bl', $_GET)) {
            $this->bl = $_GET['bl'];
            foreach ($this->bibtrans as $biblang=>$biblist) {
                foreach ($biblist as $bibid=>$bibdata) {
                    if ($bibid == $this->bl) {
                        $bibfile = __DIR__.'/../openbibles/'.$bibdata[1];
                        $this->_bibtransabbr = $bibdata[2];
                        $this->_biblexml = new CommonBible($bibfile);
                    }
                }
            }
        }

        $tmp = $this->_loadJson('data', $this->ll);
        if (array_key_exists('labels', $tmp) && is_array($tmp['labels'])) {
            $this->_labels = $tmp['labels'];
        }
        if (array_key_exists('sundays', $tmp) && is_array($tmp['sundays'])) {
            $this->_sundays = $tmp['sundays'];
        }
        if (array_key_exists('mysteries', $tmp) && is_array($tmp['mysteries'])) {
            $this->_mysteries = $tmp['mysteries'];
        }
        if (array_key_exists('bible', $tmp) && is_array($tmp['bible'])) {
            $this->_bible = $tmp['bible'];
        }
    }

    /**
     * Loads a JSON file
     *
     * @param string $dirname  Name of the directory
     * @param string $fileName Name of the file, without directory and extension
     * @param bool   $assoc    Whether to create associative arrays instead of
     *                         objects when reading a JSON
     *
     * @return array Content of the file or an empty array
     */
    private function _loadJson(string $dirname, string $fileName, $assoc = true)
    {
        if ($dirname != '') {
            $aFile = __DIR__."/../${dirname}/${fileName}.json";
        } else {
            $aFile = __DIR__."/../${fileName}.json";
        }
        if (file_exists($aFile)) {
            $aFileCont = file_get_contents($aFile);
            if ($aFileCont !== false) {
                $a = json_decode($aFileCont, $assoc);
                if ($a !== null) {
                    return $a;
                }
            }
        }
        return [];
    }

    /**
     * Changes a Bible verse reference into a placeholder (e.g.
     * `$bib[Psalms]{Ps 1.1}`) and, if Bible translation is defined, adds the
     * respective text.
     *
     * @param string $ref Bible verse reference (e.g. `Ps 1.1`)
     *
     * @return string
     */
    private function _replbb($ref)
    {
        if (preg_match('/^([A-Za-z0-9]+)\s+(.*)$/', $ref, $m) !== 1) {
            return $ref;
        }
        if (count($m) < 3) {
            return $ref;
        }
        if (!array_key_exists($m[1], $this->_bible)) {
            return $ref;
        }
        if (!array_key_exists('abbr', $this->_bible[$m[1]])) {
            return $ref;
        }
        $addition = '';
        if ($this->_biblexml !== null && array_key_exists($m[1], $this->_bibtransabbr)) {
            $addition = ' '.$this->_biblexml->getByRef($this->_bibtransabbr[$m[1]].' '.$m[2]);
        }
        return '@bib[' . $this->_bible[$m[1]]['title'] . ']{' . $this->_bible[$m[1]]['abbr'] . ' ' . $m[2] . '}'.$addition;
    }

    /**
     * This function replaces label IDs with respective label texts.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched label ID)
     *
     * @return string Text of the label or "???" if the label ID is unknown or an empty string in case of an error
     *
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replcbs(array $matches): string
    {
        if ((!is_array($this->_labels)) || count($matches) < 2) {
            return '';
        }
        return array_key_exists($matches[1], $this->_labels) ? $this->_labels[$matches[1]] : "???";
    }

    /**
     * This function replaces label IDs with respective label texts.
     *
     * It is actually the same as {@see MassData::replcbs()}, but it wraps the returned value in a "span" tag with the class "command".
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched label ID)
     * @return string Text of the label or "???" if the label ID is unknown or an empty string in case of an error, in every case wrapped as noted in the description
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replcb(array $matches): string
    {
        return "<span class=\"command\">" . $this->replcbs($matches) . "</span>";
    }

    /**
     * This function replaces Sunday IDs with respective Sunday texts.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched Sunday ID)
     * @return string Text of the Sunday or "???" if the Sunday ID is unknown or an empty string in case of an error
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replsu(array $matches): string
    {
        if ((!is_array($this->_sundays)) || count($matches) < 2) {
            return '';
        }
        return array_key_exists($matches[1], $this->_sundays) ? $this->_sundays[$matches[1]] : "???";
    }

    /**
     * This function replaces Mystery IDs with respective names of Mysteries of the Rosary.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched Mystery ID)
     * @return string Name of the Mystery or "???" if the Mystery ID is unknown or an empty string in case of an error
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replmy(array $matches): string
    {
        if ((!is_array($this->_mysteries)) || count($matches) < 2) {
            return '';
        }
        return array_key_exists($matches[1], $this->_mysteries) ? $this->_mysteries[$matches[1]] : "???";
    }

    private function replbib(array $matches): string
    {
        if (count($matches) < 3) {
            return '';
        }
        return sprintf("<abbr title=\"%s\">%s</abbr>", $matches[1], $matches[2]);
    }

    /**
     * This function replaces reading IDs with respective reading texts.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched reading ID)
     * @return string Text of the reading or "???" if the reading ID is unknown or an empty string in case of an error
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replre(string $which)
    {
        if (!array_key_exists($which, $this->reads)) {
            return '???';
        }
        $icon = '';
        switch ($which) {
            case 'r1':
                $icon = '@{read1} ';
                break;
            case 'r2':
                $icon = '@{read2} ';
                break;
            case 'p':
                $icon = '@{psalm} ';
                break;
            case 'a':
                $icon = '@{alleluia} ';
                break;
            case 'g':
                $icon = '@{readE} ';
        }

        $ret = $this->reads[$which];
        if (is_string($ret)) {
            $obj = new \StdClass();
            $obj->r = '@icon{bible} ' . $icon . ' [' . $this->_replbb($ret) . ']';
            return $obj;
        } elseif (is_array($ret)) {
            $rtn = [];
            foreach ($ret as $one) {
                $obj = new \StdClass();
                $obj->r = '@icon{bible} ' . $icon . ' [' . $this->_replbb($one) . ']';
                $rtn[] = $obj;
            }
            return $rtn;
        }
        return [];
    }

    /**
     * This function replaces icon IDs with respective Font Awesome icons.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched icon ID)
     * @return string Font Awesome icon in the form of an "i" tag with the respective CSS class or an empty string in case of an error
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replico(array $matches): string
    {
        if ((!is_array($this->_icons)) || count($matches) < 2 || (!array_key_exists($matches[1], $this->_icons))) {
            return '';
        }
        return "<i class=\"" . $this->_icons[$matches[1]] . "\"></i>";
    }

    /**
     * Regex replacement of label and icon placeholders in a text.
     *
     * @param string $text Text that may contain label/icon placeholders
     * @return string Text with replaced label/icon placeholders
     * @uses MassData::replcb()
     * @uses MassData::replico()
     */
    public function repl(string $text)
    {
        return preg_replace_callback_array(
            [
                '/@\{([A-Za-z0-9]+)\}/' => 'self::replcb',
                '/@su\{([A-Za-z0-9]+)\}/' => 'self::replsu',
                '/@my\{([A-Za-z0-9]+)\}/' => 'self::replmy',
                '/@bib\[([^\]]+)\]\{([^\}]+)\}/' => 'self::replbib',
                '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'
            ],
            htmlspecialchars($text)
        );
    }

    /**
     * Regex replacement of label and icon placeholders in a text.
     *
     * @param string $text Text that may contain label/icon placeholders
     * @return string Text with replaced label/icon placeholders
     * @uses MassData::replcbs()
     * @uses MassData::replico()
     */
    public function repls(string $text)
    {
        return preg_replace_callback_array(
            [
                '/@\{([A-Za-z0-9]+)\}/' => 'self::replcbs',
                '/@su\{([A-Za-z0-9]+)\}/' => 'self::replsu',
                '/@my\{([A-Za-z0-9]+)\}/' => 'self::replmy',
                '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico'
            ],
            htmlspecialchars($text)
        );
    }

    public function isRosary()
    {
        return array_key_exists('sn', $_GET) && $_GET['sn'] == 'rosary';
    }

    public function objToHtml2(object $obj): string
    {
        $who = '';
        $what = '';
        if (isset($obj->reading)) {
            $what = "<a href=\"" . $this->repls('@{dbrlink}') . "\">" . $this->repls('@icon{booklink} @{dbrtext}') . "</a>";
        } elseif (isset($obj->{""})) {
            $what = $this->repl($obj->{""});
        } elseif (isset($obj->{"p"})) {
            $who = "<span class=\"who\">P:</span>";
            $what = $this->repl($obj->{"p"});
        } elseif (isset($obj->{"a"})) {
            $who = "<span class=\"who\">A:</span>";
            $what = "<strong>" . $this->repl($obj->{"a"}) . "</strong>";
        } elseif (isset($obj->{"r"})) {
            $who = "<span class=\"who\">R:</span>";
            $what = $this->repl($obj->{"r"});
        }

        $cls = ($who == '') ? " class=\"command\"" : "";
        return "<div${cls}>${who}<span class=\"what\">${what}</span></div>\r\n";
    }

    public function parseToHtml($row, $deep = true)
    {
        if (is_object($row) && isset($row->read)) {
            return $this->parseToHtml($this->replre($row->read));
        } elseif (is_object($row)) {
            return $this->objToHtml2($row);
        } elseif (is_array($row)) {
            $ret = $deep ? "<div class=\"options\">\r\n" : '';
            foreach ($row as $rowrow) {
                $ret .= $deep ? "<div>\r\n" : '';
                $ret .= $this->parseToHtml($rowrow, false);
                $ret .= $deep ? "</div>\r\n" : '';
            }
            $ret .= $deep ? "</div>\r\n" : '';
            return $ret;
        }
    }

    public function htmlObj(): string
    {
        $section = $this->isRosary() ? 'rosary' : 'texts';
        $texts = $this->_loadJson('data', $this->tl, false);

        if (!isset($texts->{$section}) || !is_array($texts->{$section})) {
            return var_export($texts, true);
        }

        $ret = '';
        foreach ($texts->{$section} as $row) {
            $ret .= $this->parseToHtml($row);
        }
        return $ret;
    }
}
