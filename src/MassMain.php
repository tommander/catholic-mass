<?php
/**
 * Main class of the Order of Mass app
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\OrderOfMass;

/**
 * Main class of the Order of Mass app
 */
class MassMain
{

    /**
     * DI Container
     *
     * @var \DI\Container
     */
    private $container;

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
     * This is used to correctly initialize {@see BibleXML}
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
    public $langlist = [];

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
     * @see Lectionary::lectio()
     */
    public $reads = [];

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
    public $biblist = [];

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
    private $biblistabbr = [];

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
     * Creates a container
     */
    public function __construct()
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);
        $containerBuilder->addDefinitions(
            [
                Logger::class       => \DI\create(Logger::class)->lazy(),
                BibleXML::class     => \DI\create(BibleXML::class)->constructor(\DI\get(Logger::class))->lazy(),
                BibleIndexer::class => \DI\create(BibleIndexer::class)->constructor(\DI\get(Logger::class))->lazy(),
                Lectionary::class   => \DI\create(Lectionary::class)->constructor(\DI\get(Logger::class))->lazy(),
                Measure::class      => \DI\create(Measure::class)->constructor(\DI\get(Logger::class))->lazy(),
            ]
        );
        $this->container = $containerBuilder->build();

        $this->init();

    }//end __construct()


    /**
     * Initialize the class variables
     *
     * @return void
     */
    private function init()
    {
        $this->langlist = Helper::loadJson('assets/json/langlist.json');
        $this->biblist  = Helper::loadJson('assets/json/biblist.json');

        if (array_key_exists('ll', $_GET) === true && is_string($_GET['ll']) === true) {
            if (array_key_exists($_GET['ll'], $this->langlist) !== false) {
                if (preg_match('/^[a-z]{3}$/', $_GET['ll']) === 1) {
                    $this->ll = $_GET['ll'];
                }
            }
        }

        if (array_key_exists('tl', $_GET) === true && is_string($_GET['tl']) === true) {
            if (array_key_exists($_GET['tl'], $this->langlist) !== false) {
                if (preg_match('/^[a-z]{3}$/', $_GET['tl']) === 1) {
                    $this->tl = $_GET['tl'];
                }
            }
        }

        if (array_key_exists('bl', $_GET) === true && is_string($_GET['bl']) === true) {
            $this->bl = $_GET['bl'];
            foreach ($this->biblist as $biblang => $biblist) {
                foreach ($biblist as $bibid => $bibdata) {
                    if ($bibid === $this->bl) {
                        $this->biblistabbr = $bibdata[2];

                        $biblexml = $this->container->get(BibleXML::class);
                        $biblexml->defineFile($bibdata[1]);
                    }
                }
            }
        }

        $this->labelsJson  = Helper::loadJson('assets/json/lang/'.$this->ll.'-labels.json');
        $this->contentJson = Helper::loadJson('assets/json/lang/'.$this->tl.'-content.json', false);

        $lectionary  = $this->container->get(Lectionary::class);
        $this->reads = $lectionary->getReadings(time());

        /*
            $indexer = $this->container->get(BibleIndexer::class);
            $logger = $this->container->get(Logger::class);
            foreach ($this->biblist as $blang => $blist) {
                foreach ($blist as $bid => $bdata) {
                    $logger->debug('Indexer "'.$bdata[1].'"');
                  $indexer->createIndex($bdata[1]);
               }
            }
        */

    }//end init()


    /**
     * Creates a content of `select` HTML tag from an array
     *
     * @param array $def    Array defining the content of the combobox
     * @param bool  $simple Whether `optgroup` tags are being used
     *
     * @return string
     */
    private function comboBoxContent(array $def, bool $simple): string
    {
        $ret = '';
        if ($simple === true) {
            foreach ($def as $opt) {
                $sel = '';
                if ($opt['sel'] === true) {
                    $sel = ' selected="selected"';
                }

                $ret .= sprintf("<option value=\"%s\"%s>%s</option>\r\n", $opt['value'], $sel, $opt['text']);
            }

            return $ret;
        }

        foreach ($def as $grp => $lst) {
            if ($grp !== '') {
                $ret .= sprintf("<optgroup label=\"\">\r\n", $grp);
            }

            foreach ($lst as $opt) {
                $sel = '';
                if ($opt['sel'] === true) {
                    $sel = ' selected="selected"';
                }

                $ret .= sprintf("<option value=\"%s\"%s>%s</option>\r\n", $opt['value'], $sel, $opt['text']);
            }

            if ($grp !== '') {
                $ret .= "</optgroup>\r\n";
            }
        }

        return $ret;

    }//end comboBoxContent()


    /**
     * Creates a list of links for the main page footer
     *
     * @param array $def Array of links
     *
     * @return string
     */
    private function linksContent(array $def): string
    {
        $ret = '';
        foreach ($def as $one) {
            if ($one === 'space') {
                $ret .= "<span>&nbsp;</span>\r\n";
                continue;
            }

            if (is_array($one) !== true) {
                continue;
            }

            if (array_key_exists('licenseurl', $one) === true
                && array_key_exists('licensetext', $one) === true
            ) {
                $ret .= sprintf(
                    "<span>%s: <a href=\"%s\">%s</a> (<a href=\"%s\">%s</a>)</span>\r\n",
                    $one['label'],
                    $one['url'],
                    $one['text'],
                    $one['licenseurl'],
                    $one['licensetext']
                );
                continue;
            }

            if (array_key_exists('list', $one) === true) {
                foreach ($one['list'] as $key => $data) {
                    $author = '';
                    if ($data['author'] !== 'Tommander') {
                        $author = ' by '.$data['author'];
                    }

                    $links = '';
                    $cnt   = 1;

                    foreach ($data['link'] as $lnk) {
                        if ($cnt > 1) {
                            $links .= ', ';
                        }

                        $links .= sprintf(
                            "<a href=\"%s\">source %d</a>",
                            $lnk,
                            $cnt++
                        );
                    }

                    $ret .= sprintf(
                        "<span>%s%s (%s)</span>\r\n",
                        $data['title'],
                        $author,
                        $links
                    );
                }//end foreach

                continue;
            }//end if

            $ret .= sprintf(
                "<span>%s: <a href=\"%s\">%s</a></span>\r\n",
                $one['label'],
                $one['url'],
                $one['text'],
            );
        }//end foreach

        return $ret;

    }//end linksContent()


    /**
     * Prepares an associative array for replacement of content placeholders in an HTML template
     *
     * @return array<string, mixed> Key is a replacement placeholder, value is the content to replace with
     */
    private function prepareHtmlData()
    {

        $comboboxL = [];
        foreach ($this->langlist as $code => $info) {
            if (is_string($info['title']) !== true) {
                continue;
            }

            $comboboxL[] = [
                'value' => htmlspecialchars($code),
                'sel'   => ($code == $this->ll),
                'text'  => htmlspecialchars($info['title']),
            ];
        }

        $comboboxT = [];
        foreach ($this->langlist as $code => $info) {
            if (is_string($info['title']) !== true) {
                continue;
            }

            $comboboxT[] = [
                'value' => htmlspecialchars($code),
                'sel'   => ($code == $this->tl),
                'text'  => htmlspecialchars($info['title']),
            ];
        }

        $comboboxY = [
            [
                'value' => 'mass',
                'sel'   => !$this->isRosary(),
                'text'  => $this->repls('@{heading}'),
            ],
            [
                'value' => 'rosary',
                'sel'   => $this->isRosary(),
                'text'  => $this->repls('@{rosary}'),
            ],
        ];

        $comboboxB = [
            '' => [
                [
                    'value' => '',
                    'sel'   => false,
                    'text'  => '-',
                ],
            ],
        ];
        if (array_key_exists($this->ll, $this->biblist) === true) {
            $grplabel = $this->langlist[$this->ll]['title'];
            $comboboxB[$grplabel] = [];
            foreach ($this->biblist[$this->ll] as $bibleid => $bibledata) {
                if (isset($bibledata[0]) !== true) {
                    continue;
                }

                $comboboxB[$grplabel][] = [
                    'value' => $bibleid,
                    'sel'   => $this->bl == $bibleid,
                    'text'  => $bibledata[0],
                ];
            }
        }

        if (array_key_exists($this->tl, $this->biblist) === true && $this->ll !== $this->tl) {
            $grplabel = $this->langlist[$this->tl]['title'];
            $comboboxB[$grplabel] = [];
            foreach ($this->biblist[$this->tl] as $bibleid => $bibledata) {
                if (isset($bibledata[0]) !== true) {
                    continue;
                }

                $comboboxB[$grplabel][] = [
                    'value' => $bibleid,
                    'sel'   => $this->bl == $bibleid,
                    'text'  => $bibledata[0],
                ];
            }
        }

        $links = [
            [
                'label' => $this->repls('@{license}'),
                'text'  => 'GNU GPL v3',
                'url'   => 'https://www.gnu.org/licenses/gpl-3.0.html',
            ],
            [
                'label' => $this->repls('@{source}'),
                'text'  => 'Repository at GitHub.com'.Helper::showCommit(),
                'url'   => 'https://github.com/tommander/catholic-mass',
            ],
            [
                'label' => $this->repls('@{author}'),
                'text'  => 'Tomáš <q>Tommander</q> Rajnoha',
                'url'   => 'mailto:tommander@tommander.cz',
            ],
            'space',
            [
                'label'       => $this->repls('@{headerimg}'),
                'text'        => 'Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso',
                'url'         => 'https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg',
                'licensetext' => 'CC BY-SA 4.0',
                'licenseurl'  => 'https://creativecommons.org/licenses/by-sa/4.0',
            ],
            [
                'label'       => $this->repls('@{icons}'),
                'text'        => 'Font Awesome Free 5.15.3 by @fontawesome',
                'url'         => 'https://fontawesome.com',
                'licensetext' => 'Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License',
                'licenseurl'  => 'https://fontawesome.com/license/free',
            ],
            [
                'label'       => $this->repls('@{font}'),
                'text'        => 'Source Sans Pro by Paul D. Hunt',
                'url'         => 'https://fonts.google.com/specimen/Source+Sans+Pro',
                'licensetext' => 'Open Fonts License',
                'licenseurl'  => 'https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL',
            ],
            [
                'label' => $this->repls('@{texts}'),
                'list'  => $this->langlist,
            ],
        ];

        $title = '@{heading}';
        if ($this->isRosary() === true) {
            $title = '@{rosary}';
        }

        $dateL = time();
        if ($this->isRosary() !== true) {
            $dateL = Helper::nextSunday(time());
        }

        $dateR = '@my{'.Helper::todaysMystery(time()).'}';
        if ($this->isRosary() !== true) {
            $lectionary = $this->container->get(Lectionary::class);
            $dateR      = '@su{'.$lectionary->sundayLabel(time()).'}';
        }

        // phpcs:disable
        /**
         * @psalm-suppress InvalidArgument 
         */
        // phpcs:enable
        return [
            '/@@LANG@@/'    => $this->repls('@{html}'),
            '/@@TITLE@@/'   => $this->repls($title),
            '/@@IDXL@@/'    => $this->repls('@{idxL}'),
            '/@@IDXY@@/'    => $this->repls('@{idxY}'),
            '/@@IDXB@@/'    => $this->repls('@{idxB}'),
            '/@@IDXT@@/'    => $this->repls('@{idxT}'),
            '/@@CBL@@/'     => $this->comboBoxContent($comboboxL, true),
            '/@@CBY@@/'     => $this->comboBoxContent($comboboxY, true),
            '/@@CBB@@/'     => $this->comboBoxContent($comboboxB, false),
            '/@@CBT@@/'     => $this->comboBoxContent($comboboxT, true),
            '/@@LEGP@@/'    => $this->repls('@{lblP}'),
            '/@@LEGA@@/'    => $this->repls('@{lblA}'),
            '/@@LEGR@@/'    => $this->repls('@{lblR}'),
            '/@@DATEL@@/'   => date('d.m.Y', $dateL),
            '/@@DATER@@/'   => $this->repls($dateR),
            '/@@MAIN@@/'    => $this->htmlObj(),
            '/@@LINKS@@/'   => $this->linksContent($links),
            '/@@MEMPEAK@@/' => \memory_get_peak_usage(true),
            '/@@MEMUSE@@/'  => \memory_get_usage(true),
        ];

    }//end prepareHtmlData()


    /**
     * Runs the app (builds a final HTML)
     *
     * @return void
     */
    public function run()
    {
        $meas = $this->container->get(Measure::class);
        $meas->start();

        $template = __DIR__.'/../assets/html/main.html';
        if (file_exists($template) !== true) {
            return;
        }

        $htmldata = $this->prepareHtmlData();
        $content  = file_get_contents($template);
        $content  = preg_replace(
            array_keys($htmldata),
            array_values($htmldata),
            $content
        );

        echo $content;
        echo "<!-- \r\n";
        var_export($meas->finish());
        echo "\r\n -->\r\n";
        return;

    }//end run()


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

        $addition = '';
        if (array_key_exists($m[1], $this->biblistabbr) === true) {
            $biblexml = $this->container->get(BibleXML::class);
            $addition = ' '.$biblexml->getByRef($this->biblistabbr[$m[1]].' '.$m[2]);
        }

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
        $section = 'mass';
        if ($this->isRosary() === true) {
            $section = 'rosary';
        }

        if (isset($this->contentJson->{$section}) === false || is_array($this->contentJson->{$section}) === false) {
            return var_export($this->contentJson, true);
        }

        $ret = '';
        foreach ($this->contentJson->{$section} as $row) {
            $ret .= $this->parseToHtml($row);
        }

        return $ret;

    }//end htmlObj()


}//end class
