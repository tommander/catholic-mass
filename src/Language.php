<?php
/**
 * Language unit
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
 * Language class
 */
class Language
{
    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    private $logger;

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
     * Content of `assets/json/lang/lng-labels.json`, where `lng` is a three-code letter of a language stored in {@see MassMain::$ll}
     *
     * @var array
     */
    private $labelsJson = [];

    /**
     * Content of `assets/json/lang/lng-content.json`, where `lng` is a three-code letter of a language stored in {@see MassMain::$tl}
     *
     * @var array
     */
    private $contentJson = [];

    private $getParams;

    private $bibleXML;

    private $lectionary;


    public function __construct(LoggerInterface $logger, GetParams $getParams, BibleXML $bibleXML, Lectionary $lectionary)
    {
        $this->labelsJson  = Helper::loadJson('assets/json/lang/'.$this->ll.'-labels.json');
        $this->contentJson = Helper::loadJson('assets/json/lang/'.$this->tl.'-content.json', false);
        $this->logger      = $logger;
        $this->getParams   = $getParams;
        $this->bibleXML    = $bibleXML;
        $this->lectionary  = $lectionary;
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
    private function replbb($ref)
    {
        if (preg_match('/^([A-Za-z0-9]+)\s+(.*)$/', $ref, $m) !== 1
            || count($m) < 3
            || array_key_exists($m[1], $this->labelsJson['bible']) === false
            || array_key_exists('abbr', $this->labelsJson['bible'][$m[1]]) === false
        ) {
            return $ref;
        }

        $addition = $this->bibleXML->getByRef($m[1], $m[2]);
        /*if (array_key_exists($m[1], $this->biblistabbr) === true) {
            $biblexml = $this->container->get(BibleXML::class);
            $addition = ' '.$biblexml->getByRef($this->biblistabbr[$m[1]].' '.$m[2]);
        }*/

        return '@bib['.$this->labelsJson['bible'][$m[1]]['title'].']{'.$this->labelsJson['bible'][$m[1]]['abbr'].' '.$m[2].'}'.$addition;
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
            || array_key_exists($matches[1], $this->labelsJson['labels']) === false
        ) {
            return '';
        }

        return $this->labelsJson['labels'][$matches[1]];
    }//end replcbs()


    /**
     * This function replaces label IDs with respective label texts.
     *
     * It is actually the same as {@see MassMain::replcbs()}, but it wraps the returned value in a "span" tag with the class "command".
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
            || array_key_exists($matches[1], $this->labelsJson['sundays']) === false
        ) {
            return '';
        }

        return $this->labelsJson['sundays'][$matches[1]];
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
            || array_key_exists($matches[1], $this->labelsJson['mysteries']) === false
        ) {
            return '';
        }

        return $this->labelsJson['mysteries'][$matches[1]];
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
        $reads = $this->lectionary->getReadings(time());
        if (array_key_exists($which, $reads) === false) {
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

        $ret = $reads[$which];
        if (is_string($ret) === true) {
            $obj    = new \stdClass();
            $obj->r = '@icon{bible} '.$icon.' ['.$this->replbb($ret).']';
            return $obj;
        } elseif (is_array($ret) === true) {
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
     * @uses   MassMain::replcb()
     * @uses   MassMain::replico()
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
     * @uses   MassMain::replcbs()
     * @uses   MassMain::replico()
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
}//end class
