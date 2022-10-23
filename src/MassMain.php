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
     * Creates a container
     */
    public function __construct()
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);
        $containerBuilder->addDefinitions(
            [
                CommonBible::class  => \DI\create(CommonBible::class)->lazy(),
                MassData::class     => \DI\create(MassData::class)->constructor(\DI\get(CommonBible::class))->lazy(),
                MassReadings::class => \DI\create(MassReadings::class)->lazy(),
                Measure::class      => \DI\create(Measure::class)->lazy(),
            ]
        );
        $this->container = $containerBuilder->build();

    }//end __construct()


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
        $massData        = $this->container->get(MassData::class);
        $massReadings    = $this->container->get(MassReadings::class);
        $massData->reads = $massReadings->lectio();

        $comboboxL = [];
        foreach ($massData->langs as $code => $info) {
            $comboboxL[] = [
                'value' => htmlspecialchars($code),
                'sel'   => ($code == $massData->ll),
                'text'  => htmlspecialchars($info['title']),
            ];
        }

        $comboboxT = [];
        foreach ($massData->langs as $code => $info) {
            $comboboxT[] = [
                'value' => htmlspecialchars($code),
                'sel'   => ($code == $massData->tl),
                'text'  => htmlspecialchars($info['title']),
            ];
        }

        $comboboxY = [
            [
                'value' => 'mass',
                'sel'   => !$massData->isRosary(),
                'text'  => $massData->repls('@{heading}'),
            ],
            [
                'value' => 'rosary',
                'sel'   => $massData->isRosary(),
                'text'  => $massData->repls('@{rosary}'),
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
        if (array_key_exists($massData->ll, $massData->bibtrans) === true) {
            $grplabel = $massData->langs[$massData->ll]['title'];
            $comboboxB[$grplabel] = [];
            foreach ($massData->bibtrans[$massData->ll] as $bibleid => $bibledata) {
                $comboboxB[$grplabel][] = [
                    'value' => $bibleid,
                    'sel'   => $massData->bl == $bibleid,
                    'text'  => $bibledata[0],
                ];
            }
        }

        if (array_key_exists($massData->tl, $massData->bibtrans) === true && $massData->ll !== $massData->tl) {
            $grplabel = $massData->langs[$massData->tl]['title'];
            $comboboxB[$grplabel] = [];
            foreach ($massData->bibtrans[$massData->tl] as $bibleid => $bibledata) {
                $comboboxB[$grplabel][] = [
                    'value' => $bibleid,
                    'sel'   => $massData->bl == $bibleid,
                    'text'  => $bibledata[0],
                ];
            }
        }

        $links = [
            [
                'label' => $massData->repls('@{license}'),
                'text'  => 'GNU GPL v3',
                'url'   => 'https://www.gnu.org/licenses/gpl-3.0.html',
            ],
            [
                'label' => $massData->repls('@{source}'),
                'text'  => 'Repository at GitHub.com'.MassHelper::showCommit(),
                'url'   => 'https://github.com/tommander/catholic-mass',
            ],
            [
                'label' => $massData->repls('@{author}'),
                'text'  => 'Tomáš <q>Tommander</q> Rajnoha',
                'url'   => 'mailto:tommander@tommander.cz',
            ],
            'space',
            [
                'label'       => $massData->repls('@{headerimg}'),
                'text'        => 'Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso',
                'url'         => 'https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg',
                'licensetext' => 'CC BY-SA 4.0',
                'licenseurl'  => 'https://creativecommons.org/licenses/by-sa/4.0',
            ],
            [
                'label'       => $massData->repls('@{icons}'),
                'text'        => 'Font Awesome Free 5.15.3 by @fontawesome',
                'url'         => 'https://fontawesome.com',
                'licensetext' => 'Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License',
                'licenseurl'  => 'https://fontawesome.com/license/free',
            ],
            [
                'label'       => $massData->repls('@{font}'),
                'text'        => 'Source Sans Pro by Paul D. Hunt',
                'url'         => 'https://fonts.google.com/specimen/Source+Sans+Pro',
                'licensetext' => 'Open Fonts License',
                'licenseurl'  => 'https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL',
            ],
            [
                'label' => $massData->repls('@{texts}'),
                'list'  => $massData->langs,
            ],
        ];

        $title = '@{heading}';
        if ($massData->isRosary() === true) {
            $title = '@{rosary}';
        }

        $dateL = time();
        if ($massData->isRosary() !== true) {
            $dateL = $massReadings->nextSunday();
        }

        $dateR = '@su{'.$massReadings->sundayLabel().'}';
        if ($massData->isRosary() === true) {
            $dateR = '@my{'.$massReadings->todaysMystery().'}';
        }

        return [
            '/@@LANG@@/'    => $massData->repls('@{html}'),
            '/@@TITLE@@/'   => $massData->repls($title),
            '/@@IDXL@@/'    => $massData->repls('@{idxL}'),
            '/@@IDXY@@/'    => $massData->repls('@{idxY}'),
            '/@@IDXB@@/'    => $massData->repls('@{idxB}'),
            '/@@IDXT@@/'    => $massData->repls('@{idxT}'),
            '/@@CBL@@/'     => $this->comboBoxContent($comboboxL, true),
            '/@@CBY@@/'     => $this->comboBoxContent($comboboxY, true),
            '/@@CBB@@/'     => $this->comboBoxContent($comboboxB, false),
            '/@@CBT@@/'     => $this->comboBoxContent($comboboxT, true),
            '/@@LEGP@@/'    => $massData->repls('@{lblP}'),
            '/@@LEGA@@/'    => $massData->repls('@{lblA}'),
            '/@@LEGR@@/'    => $massData->repls('@{lblR}'),
            '/@@DATEL@@/'   => date('d.m.Y', $dateL),
            '/@@DATER@@/'   => $massData->repls($dateR),
            '/@@MAIN@@/'    => $massData->htmlObj(),
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
