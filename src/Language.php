<?php
/**
 * Language unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
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
     * @var Logger
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

    /**
     * GetParams instance
     *
     * @var GetParams
     */
    private $getParams;

    /**
     * BibleReader instance
     *
     * @var BibleReader
     */
    private $bibleReader;

    /**
     * Lectionary instance
     *
     * @var Lectionary
     */
    private $lectionary;

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
    private array $langlist = [];


    /**
     * Hello
     *
     * @param Logger      $logger      Hello
     * @param GetParams   $getParams   Hello
     * @param BibleReader $bibleReader Hello
     * @param Lectionary  $lectionary  Hello
     */
    public function __construct(Logger $logger, GetParams $getParams, BibleReader $bibleReader, Lectionary $lectionary)
    {
        $this->logger      = $logger;
        $this->getParams   = $getParams;
        $this->bibleReader = $bibleReader;
        $this->lectionary  = $lectionary;

        $this->langlist    = Helper::loadJson('assets/json/langlist.json');
        $this->labelsJson  = Helper::loadJson('assets/json/lang/'.$this->getParams->getLabelLang().'-labels.json');
        $this->contentJson = Helper::loadJson('assets/json/lang/'.$this->getParams->getContentLang().'-content.json', false);

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
    public function replbb($ref)
    {
        $addition = $this->bibleReader->getVerse($ref);
        if ($addition !== '') {
            $addition = ' '.$addition;
        }

        $locRef   = $ref;
        $bookFull = '';
        if (preg_match('/^(\S+)(.*)$/', $ref, $m) === 1 && isset($m[1]) === true && isset($m[2]) === true) {
            $book     = $m[1];
            $bookFull = $this->bibleReader->bookAbbrToFull($book);
            $locBook  = $this->bibleReader->localizedAbbr($book);
            if ($locBook !== false) {
                $locRef   = $locBook['short'].$m[2];
                $bookFull = $locBook['full'];
            }
        }

        return '@bib['.$bookFull.']{'.$locRef.'}'.$addition;

    }//end replbb()


    /**
     * Hello
     *
     * @param string $lang Hello
     * @param string $data Hello
     *
     * @return mixed
     */
    public function getLanguageData(string $lang, string $data)
    {
        if ($lang === '' && $data === '') {
            return $this->langlist;
        }

        if (isset($this->langlist[$lang]) !== true) {
            return null;
        }

        if (isset($this->langlist[$lang][$data]) !== true) {
            return null;
        }

        return $this->langlist[$lang][$data];

    }//end getLanguageData()


    /**
     * Hello
     *
     * @param string $type Hello
     *
     * @return string|array
     */
    public function getContent(string $type): string|array
    {
        if ($type === 'bible') {
            return $this->bibleReader->renderBible();
        }

        if ($type !== 'rosary' && $type !== 'mass') {
            return '';
        }

        return $this->contentJson->{$type};

    }//end getContent()


    /**
     * Hello
     *
     * @param string $selection Hello
     *
     * @return array
     */
    public function getLanguageComboList(string $selection=''): array
    {
        $ret = [];
        foreach ($this->langlist as $code => $info) {
            if (is_string($info['title']) !== true) {
                continue;
            }

            $ret[] = [
                'value' => htmlspecialchars($code),
                'sel'   => ($code == $selection),
                'text'  => htmlspecialchars($info['title']),
            ];
        }

        return $ret;

    }//end getLanguageComboList()


    /**
     * Regex replacement of label and icon placeholders in a text.
     *
     * @param string $text        Text that may contain label/icon placeholders
     * @param bool   $wrapCommand Whether to wrap label placeholders with `span` tag with class `command` (default `false`)
     *
     * @return string Text with replaced label/icon placeholders
     *
     * @psalm-suppress MissingClosureParamType
     */
    public function repls(string $text, bool $wrapCommand=false)
    {
        $ret = preg_replace_callback_array(
            [
                '/@\{([A-Za-z0-9]+)\}/'           => function ($matches) use ($wrapCommand) {
                    if (count($matches) < 2
                        || array_key_exists($matches[1], $this->labelsJson['labels']) === false
                    ) {
                        return '';
                    }

                    $ret = $this->labelsJson['labels'][$matches[1]];
                    if ($wrapCommand !== true) {
                        return $ret;
                    }

                    return "<span class=\"command\">${ret}</span>";
                },
                '/@su\{([A-Za-z0-9]+)\}/'         => function ($matches) {
                    if (count($matches) < 2
                        || array_key_exists($matches[1], $this->labelsJson['sundays']) === false
                    ) {
                        return '';
                    }

                    return $this->labelsJson['sundays'][$matches[1]];
                },
                '/@my\{([A-Za-z0-9]+)\}/'         => function ($matches) {
                    if (count($matches) < 2
                        || array_key_exists($matches[1], $this->labelsJson['mysteries']) === false
                    ) {
                        return '';
                    }

                    return $this->labelsJson['mysteries'][$matches[1]];
                },
                '/@bib\[([^\]]+)?\]\{([^\}]+)\}/' => function ($matches) {
                    if (count($matches) < 3) {
                        return '';
                    }

                    return sprintf("<br><abbr title=\"%s\">%s</abbr><br>", $matches[1], $matches[2]);
                },
                '/@icon\{([A-Za-z0-9]+)\}/'       => function ($matches) {
                    if (count($matches) < 2
                        || array_key_exists($matches[1], $this->icons) === false
                    ) {
                        return '';
                    }

                    return "<i class=\"".$this->icons[$matches[1]]."\"></i>";
                },
            ],
            htmlspecialchars($text)
        );
        if (is_string($ret) === true) {
            return $ret;
        }

        return '';

    }//end repls()


}//end class
