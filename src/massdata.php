<?php
/**
 * Main file for the Order of Mass app
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
    private $biblexml;

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
     * This is used to correctly initialize {@see MassData::$biblexml}
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
    private $labels = [];

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
    private $sundays = [];

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
    private $mysteries = [];

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
     * @var array
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
    private $bible = [];

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
    private $bibtransabbr = [];

    /**
     * List of available Font Awesome icons
     *
     * - Array key is the icon ID as used in the placeholder `@icon{iconID}`
     * - Array value is the CSS class of the `<i>` tag that Font Awesome uses
     *
     * @var array<string, string>
     */
    private $icons = [
        'cross'    => 'fas fa-cross',
        'bible'    => 'fas fa-bible',
        'bubble'   => 'far fa-comment',
        'peace'    => 'far fa-handshake',
        'walk'     => 'fas fa-hiking',
        'stand'    => 'fas fa-male',
        'sit'      => 'fas fa-chair',
        'kneel'    => 'fas fa-pray',
        'booklink' => 'fas fa-book-reader',
        'bread'    => 'fas fa-cookie-bite',
        'wine'     => 'fas fa-wine-glass-alt',
    ];


    /**
     * Class constructor that initializes internal properties
     *
     * Sets {@see MassData::$tl} and {@see MassData::$ll}
     * and then loads content from language files to
     * {@see MassData::$langs} and {@see MassData::$labels}
     *
     * @param CommonBible $commonBible Hello
     */
    public function __construct(CommonBible $commonBible)
    {
        $this->langs    = MassHelper::loadJson('assets/json/langlist.json');
        $this->bibtrans = MassHelper::loadJson('assets/json/biblist.json');
        $this->biblexml = $commonBible;

        if (array_key_exists('ll', $_GET) === true && is_string($_GET['ll']) === true) {
            if (array_key_exists($_GET['ll'], $this->langs) !== false) {
                if (preg_match('/^[a-z]{3}$/', $_GET['ll']) === 1) {
                    $this->ll = $_GET['ll'];
                }
            }
        }

        if (array_key_exists('tl', $_GET) === true && is_string($_GET['tl']) === true) {
            if (array_key_exists($_GET['tl'], $this->langs) !== false) {
                if (preg_match('/^[a-z]{3}$/', $_GET['tl']) === 1) {
                    $this->tl = $_GET['tl'];
                }
            }
        }

        if (array_key_exists('bl', $_GET) === true && is_string($_GET['bl']) === true) {
            $this->bl = $_GET['bl'];
            foreach ($this->bibtrans as $biblang => $biblist) {
                foreach ($biblist as $bibid => $bibdata) {
                    if ($bibid === $this->bl) {
                        $bibfile = __DIR__.'/../libs/open-bibles/'.$bibdata[1];

                        $this->bibtransabbr = $bibdata[2];
                        $this->biblexml->defineFile($bibfile);
                    }
                }
            }
        }

        $tmp = MassHelper::loadJson('assets/json/lang/'.$this->ll.'.json');
        if (array_key_exists('labels', $tmp) === true && is_array($tmp['labels']) === true) {
            $this->labels = $tmp['labels'];
        }

        if (array_key_exists('sundays', $tmp) === true && is_array($tmp['sundays']) === true) {
            $this->sundays = $tmp['sundays'];
        }

        if (array_key_exists('mysteries', $tmp) === true && is_array($tmp['mysteries']) === true) {
            $this->mysteries = $tmp['mysteries'];
        }

        if (array_key_exists('bible', $tmp) === true && is_array($tmp['bible']) === true) {
            $this->bible = $tmp['bible'];
        }

    }//end __construct()


    /**
     * Changes a Bible verse reference into a placeholder (e.g.
     * `$bib[Psalms]{Ps 1.1}`) and, if Bible translation is defined, adds the
     * respective text.
     *
     * @param string $ref Bible verse reference (e.g. `Ps 1.1`)
     *
     * @return string
     */
    private function replbb($ref)
    {
        if (preg_match('/^([A-Za-z0-9]+)\s+(.*)$/', $ref, $m) !== 1) {
            return $ref;
        }

        if (count($m) < 3) {
            return $ref;
        }

        if (array_key_exists($m[1], $this->bible) === false) {
            return $ref;
        }

        if (array_key_exists('abbr', $this->bible[$m[1]]) === false) {
            return $ref;
        }

        $addition = '';
        if (array_key_exists($m[1], $this->bibtransabbr) === true) {
            $addition = ' '.$this->biblexml->getByRef($this->bibtransabbr[$m[1]].' '.$m[2]);
        }

        return '@bib['.$this->bible[$m[1]]['title'].']{'.$this->bible[$m[1]]['abbr'].' '.$m[2].'}'.$addition;

    }//end replbb()


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
        if (count($matches) < 2
            || array_key_exists($matches[1], $this->labels) === false
        ) {
            return '';
        }

        return $this->labels[$matches[1]];

    }//end replcbs()


    /**
     * This function replaces label IDs with respective label texts.
     *
     * It is actually the same as {@see MassData::replcbs()}, but it wraps the returned value in a "span" tag with the class "command".
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched label ID)
     *
     * @return string Text of the label or "???" if the label ID is unknown or an empty string in case of an error, in every case wrapped as noted in the description
     * @see    https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replcb(array $matches): string
    {
        return "<span class=\"command\">".$this->replcbs($matches)."</span>";

    }//end replcb()


    /**
     * This function replaces Sunday IDs with respective Sunday texts.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched Sunday ID)
     *
     * @return string Text of the Sunday or "???" if the Sunday ID is unknown or an empty string in case of an error
     * @see    https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replsu(array $matches): string
    {
        if (count($matches) < 2
            || array_key_exists($matches[1], $this->sundays) === false
        ) {
            return '';
        }

        return $this->sundays[$matches[1]];

    }//end replsu()


    /**
     * This function replaces Mystery IDs with respective names of Mysteries of the Rosary.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched Mystery ID)
     *
     * @return string Name of the Mystery or "???" if the Mystery ID is unknown or an empty string in case of an error
     * @see    https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replmy(array $matches): string
    {
        if (count($matches) < 2
            || array_key_exists($matches[1], $this->mysteries) === false
        ) {
            return '';
        }

        return $this->mysteries[$matches[1]];

    }//end replmy()


    /**
     * Replaces bible verse reference placeholders with translated references and book names in the mouse-hover hint.
     *
     * @param string[] $matches Matches of the regex function.
     *
     * @return string
     * @see    https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replbib(array $matches): string
    {
        if (count($matches) < 3) {
            return '';
        }

        return sprintf("<abbr title=\"%s\">%s</abbr>", $matches[1], $matches[2]);

    }//end replbib()


    /**
     * This function replaces reading IDs with respective reading texts.
     *
     * Reading ID can be:
     *
     * - `r1` for first reading;
     * - `r2` for second reading;
     * - `p` for responsorial psalm;
     * - `a` for alleluia;
     * - `g` for gospel
     *
     * @param string $which Reading ID
     *
     * @return \stdClass|\stdClass[]|null Text of the reading or "???" if the reading ID is unknown or an empty string in case of an error
     * @see    https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replre(string $which)
    {
        if (array_key_exists($which, $this->reads) === false) {
            return null;
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
        if (is_string($ret) === true) {
            $obj    = new \stdClass();
            $obj->r = '@icon{bible} '.$icon.' ['.$this->replbb($ret).']';
            return $obj;
        } else if (is_array($ret) === true) {
            $rtn = [];
            foreach ($ret as $one) {
                $obj    = new \stdClass();
                $obj->r = '@icon{bible} '.$icon.' ['.$this->replbb($one).']';
                $rtn[]  = $obj;
            }

            return $rtn;
        }

        return null;

    }//end replre()


    /**
     * This function replaces icon IDs with respective Font Awesome icons.
     *
     * @param string[] $matches Matches of the regex function. Should contain at least two items (0th as the complete string and 1st as the matched icon ID)
     *
     * @return string Font Awesome icon in the form of an "i" tag with the respective CSS class or an empty string in case of an error
     * @see    https://www.php.net/manual/en/function.preg-replace-callback-array
     */
    private function replico(array $matches): string
    {
        if (count($matches) < 2
            || array_key_exists($matches[1], $this->icons) === false
        ) {
            return '';
        }

        return "<i class=\"".$this->icons[$matches[1]]."\"></i>";

    }//end replico()


    /**
     * Regex replacement of label and icon placeholders in a text.
     *
     * @param string $text Text that may contain label/icon placeholders
     *
     * @return string Text with replaced label/icon placeholders
     * @uses   MassData::replcb()
     * @uses   MassData::replico()
     */
    public function repl(string $text)
    {
        $ret = preg_replace_callback_array(
            [
                '/@\{([A-Za-z0-9]+)\}/'          => 'self::replcb',
                '/@su\{([A-Za-z0-9]+)\}/'        => 'self::replsu',
                '/@my\{([A-Za-z0-9]+)\}/'        => 'self::replmy',
                '/@bib\[([^\]]+)\]\{([^\}]+)\}/' => 'self::replbib',
                '/@icon\{([A-Za-z0-9]+)\}/'      => 'self::replico',
            ],
            htmlspecialchars($text)
        );
        if (is_string($ret) === true) {
            return $ret;
        }

        return '';

    }//end repl()


    /**
     * Regex replacement of label and icon placeholders in a text.
     *
     * @param string $text Text that may contain label/icon placeholders
     *
     * @return string Text with replaced label/icon placeholders
     * @uses   MassData::replcbs()
     * @uses   MassData::replico()
     */
    public function repls(string $text)
    {
        $ret = preg_replace_callback_array(
            [
                '/@\{([A-Za-z0-9]+)\}/'     => 'self::replcbs',
                '/@su\{([A-Za-z0-9]+)\}/'   => 'self::replsu',
                '/@my\{([A-Za-z0-9]+)\}/'   => 'self::replmy',
                '/@icon\{([A-Za-z0-9]+)\}/' => 'self::replico',
            ],
            htmlspecialchars($text)
        );
        if (is_string($ret) === true) {
            return $ret;
        }

        return '';

    }//end repls()


    /**
     * Checks whether rosary was chosen as the content type
     *
     * @return bool
     */
    public function isRosary()
    {
        return array_key_exists('sn', $_GET) === true && $_GET['sn'] === 'rosary';

    }//end isRosary()


    /**
     * Convert original JSON object to an HTML representation
     *
     * @param object $obj JSON object
     *
     * @return string
     */
    public function objToHtml2(object $obj): string
    {
        $who  = '';
        $what = '';
        $cls  = '';

        if (isset($obj->reading) === true) {
            $what = "<a href=\"".$this->repls('@{dbrlink}')."\">".$this->repls('@icon{booklink} @{dbrtext}')."</a>";
        } else if (isset($obj->{""}) === true) {
            $what = $this->repl($obj->{""});
        } else if (isset($obj->{"p"}) === true) {
            $who  = "<span class=\"who\">P:</span>";
            $what = $this->repl($obj->{"p"});
        } else if (isset($obj->{"a"}) === true) {
            $who  = "<span class=\"who\">A:</span>";
            $what = "<strong>".$this->repl($obj->{"a"})."</strong>";
        } else if (isset($obj->{"r"}) === true) {
            $who  = "<span class=\"who\">R:</span>";
            $what = $this->repl($obj->{"r"});
        }

        if ($who === '') {
            $cls = ' class="command"';
        }

        return "<div${cls}>${who}<span class=\"what\">${what}</span></div>\r\n";

    }//end objToHtml2()


    /**
     * Parses one item from JSON content array into HTML
     *
     * @param mixed $row  JSON content array item
     * @param mixed $deep Flag to make sure sublevel arrays do not create unnecessary `div` tags
     *
     * @return string
     */
    public function parseToHtml($row, $deep=true)
    {
        if (is_object($row) === true && isset($row->read) === true) {
            return $this->parseToHtml($this->replre($row->read));
        } else if (is_object($row) === true) {
            return $this->objToHtml2($row);
        } else if (is_array($row) === true) {
            $ret = '';
            if ($deep === true) {
                $ret = "<div class=\"options\">\r\n";
            }

            foreach ($row as $rowrow) {
                if ($deep === true) {
                    $ret .= "<div>\r\n";
                }

                $ret .= $this->parseToHtml($rowrow, false);

                if ($deep === true) {
                    $ret .= "</div>\r\n";
                }
            }

            if ($deep === true) {
                $ret .= "</div>\r\n";
            }

            return $ret;
        }//end if

        return '';

    }//end parseToHtml()


    /**
     * Returns complete mass/rosary HTML content
     *
     * @return string
     */
    public function htmlObj(): string
    {
        $section = 'texts';
        if ($this->isRosary() === true) {
            $section = 'rosary';
        }

        $texts = MassHelper::loadJson('assets/json/lang/'.$this->tl.'.json', false);

        if (isset($texts->{$section}) === false || is_array($texts->{$section}) === false) {
            return var_export($texts, true);
        }

        $ret = '';
        foreach ($texts->{$section} as $row) {
            $ret .= $this->parseToHtml($row);
        }

        return $ret;

    }//end htmlObj()


}//end class
