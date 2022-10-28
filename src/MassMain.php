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
    //public $reads = [];


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
                LoggerInterface::class => \DI\create(Logger::class)->lazy(),
                BibleXML::class        => \DI\create(BibleXML::class)->constructor(\DI\get(LoggerInterface::class))->lazy(),
                BibleIndexer::class    => \DI\create(BibleIndexer::class)->constructor(\DI\get(LoggerInterface::class))->lazy(),
                Lectionary::class      => \DI\create(Lectionary::class)->constructor(\DI\get(LoggerInterface::class))->lazy(),
                Measure::class         => \DI\create(Measure::class)->constructor(\DI\get(LoggerInterface::class))->lazy(),
                GetParams::class       => \DI\create(GetParams::class)->constructor(\DI\get(LoggerInterface::class))->lazy(),
                HtmlMaker::class       => \DI\create(HtmlMaker::class)->constructor(\DI\get(LoggerInterface::class))->lazy(),
                Language::class        => \DI\create(Language::class)->constructor(
                    \DI\get(LoggerInterface::class),
                    \DI\get(GetParams::class),
                    \DI\get(BibleXML::class),
                    \DI\get(Lectionary::class)
                )->lazy(),
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

//        $lectionary  = $this->container->get(Lectionary::class);
//        $this->reads = $lectionary->getReadings(time());

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
}//end class
