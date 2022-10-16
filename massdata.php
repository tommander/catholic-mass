<?php

/**
 * @package OrderOfMass
 */

if (!defined('OOM_BASE')) {
    die('This file cannot be viewed independently.');
}

require __DIR__.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'bibleread.php';

/**
 * Class that helps to convert language JSON files to HTML
 */
class MassData
{
    /**
     * Hello
     * 
     * @var BibleRead 
     */
    private $bibleread;
    /** @var string $tl Texts language */
    public $tl = 'eng';
    /** @var string $ll Labels language */
    public $ll = 'eng';
    /** @var string $bl Bible translation */
    public $bl = '';
    /** @var array $langs List of languages (from data/langlist.json) */
    public $langs = [];
    /**
     * Hello
     * 
     * @var array List of labels (from data/lng.json)
     */
    private $labels = [];
    /** */
    private $sundays = [];
    /** */
    private $mysteries = [];
    /** */
    public $reads = [];
    /** */
    public $bible = [];
    /** */
    public $bibtrans = [];
    /** */
    public $bibtransabbr = [];
    /** @var array $icons List of Font Awesome icons [iconid => iconclass] */
    private $icons = [
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
     * Sets {@see MassData::$tl} and {@see MassData::$ll} and then loads content from language files to {@see MassData::$langs} and {@see MassData::$labels}
     * @return void
     */
    public function __construct()
    {
        $this->langs = $this->loadJson('data', 'langlist');
        $this->bibtrans = $this->loadJson('', 'biblist');
        $this->bibleread = null;

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
                        $bibfile = __DIR__.DIRECTORY_SEPARATOR.'openbibles'.DIRECTORY_SEPARATOR.$bibdata[1];
                        $this->bibtransabbr = $bibdata[2];
                        if (str_ends_with($bibdata[1], 'zefania.xml')) {
                            $this->bibleread = new ZefaniaBible($bibfile);
                        } elseif (str_ends_with($bibdata[1], 'usfx.xml')) {
                            $this->bibleread = new UsfxBible($bibfile);
                        } elseif (str_ends_with($bibdata[1], 'osis.xml')) {
                            $this->bibleread = new OsisBible($bibfile);
                        }
                    }
                }
            }
        }

        $tmp = $this->loadJson('data', $this->ll);
        if (array_key_exists('labels', $tmp) && is_array($tmp['labels'])) {
            $this->labels = $tmp['labels'];
        }
        if (array_key_exists('sundays', $tmp) && is_array($tmp['sundays'])) {
            $this->sundays = $tmp['sundays'];
        }
        if (array_key_exists('mysteries', $tmp) && is_array($tmp['mysteries'])) {
            $this->mysteries = $tmp['mysteries'];
        }
        if (array_key_exists('bible', $tmp) && is_array($tmp['bible'])) {
            $this->bible = $tmp['bible'];
        }
    }

    /**
     * Loads a JSON language file into an associative array
     *
     * @param string $fileName Name of the file, without directory and extension
     * @return array Content of the file or an empty array
     */
    private function loadJson(string $dirname, string $fileName, $assoc = true)
    {
        if ($dirname != '') {
            $aFile = __DIR__ . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $fileName . '.json';
        } else {
            $aFile = __DIR__ . DIRECTORY_SEPARATOR . $fileName . '.json';
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

    private function replbb($ref)
    {
        if (preg_match('/^([A-Za-z0-9]+)\s+(.*)$/', $ref, $m) !== 1) {
            return $ref;
        }
        if (count($m) < 3) {
            return $ref;
        }
        if (!array_key_exists($m[1], $this->bible)) {
            return $ref;
        }
        if (!array_key_exists('abbr', $this->bible[$m[1]])) {
            return $ref;
        }
        $addition = '';
        if ($this->bibleread !== NULL && array_key_exists($m[1], $this->bibtransabbr)) {
            $addition = ' '.$this->bibleread->getByRef($this->bibtransabbr[$m[1]].' '.$m[2]);
        }
        return '@bib[' . $this->bible[$m[1]]['title'] . ']{' . $this->bible[$m[1]]['abbr'] . ' ' . $m[2] . '}'.$addition;
    }

    /**
     * This function replaces label IDs with respective label texts.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched label ID)
     * @return string Text of the label or "???" if the label ID is unknown or an empty string in case of an error
     * @see https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replcbs(array $matches): string
    {
        if ((!is_array($this->labels)) || count($matches) < 2) {
            return '';
        }
        return array_key_exists($matches[1], $this->labels) ? $this->labels[$matches[1]] : "???";
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
        if ((!is_array($this->sundays)) || count($matches) < 2) {
            return '';
        }
        return array_key_exists($matches[1], $this->sundays) ? $this->sundays[$matches[1]] : "???";
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
        if ((!is_array($this->mysteries)) || count($matches) < 2) {
            return '';
        }
        return array_key_exists($matches[1], $this->mysteries) ? $this->mysteries[$matches[1]] : "???";
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
            $obj = new StdClass();
            $obj->r = '@icon{bible} ' . $icon . ' [' . $this->replbb($ret) . ']';
            return $obj;
        } elseif (is_array($ret)) {
            $rtn = [];
            foreach ($ret as $one) {
                $obj = new StdClass();
                $obj->r = '@icon{bible} ' . $icon . ' [' . $this->replbb($one) . ']';
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
        if ((!is_array($this->icons)) || count($matches) < 2 || (!array_key_exists($matches[1], $this->icons))) {
            return '';
        }
        return "<i class=\"" . $this->icons[$matches[1]] . "\"></i>";
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
        $texts = $this->loadJson('data', $this->tl, false);

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
