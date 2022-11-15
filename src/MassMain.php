<?php
/**
 * Main class of the Order of Mass app
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
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
     * Base URL of the web
     *
     * @var string
     */
    private $baseurl;


    /**
     * Creates a container
     *
     * @param string $baseurl Base URL with trailing slash
     */
    public function __construct(string $baseurl)
    {
        $this->baseurl = $baseurl;

        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);
        $containerBuilder->addDefinitions(
            [
                Logger::class       => \DI\create(Logger::class)->lazy(),
                GetParams::class    => \DI\create(GetParams::class)->constructor(\DI\get(Logger::class))->lazy(),
                BibleXML::class     => \DI\create(BibleXML::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(GetParams::class),
                    \DI\get(Language::class)
                )->lazy(),
                BibleIndexer::class => \DI\create(BibleIndexer::class)->constructor(\DI\get(Logger::class))->lazy(),
                Lectionary::class   => \DI\create(Lectionary::class)->constructor(\DI\get(Logger::class))->lazy(),
                Measure::class      => \DI\create(Measure::class)->constructor(\DI\get(Logger::class))->lazy(),
                HtmlMaker::class    => \DI\create(HtmlMaker::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(Language::class)
                )->lazy(),
                Language::class     => \DI\create(Language::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(GetParams::class),
                    \DI\get(BibleXML::class),
                    \DI\get(Lectionary::class)
                )->lazy(),
            ]
        );
        $this->container = $containerBuilder->build();

    }//end __construct()


    /**
     * Prepares an associative array for replacement of content placeholders in an HTML template
     *
     * @return array<string, mixed> Key is a replacement placeholder, value is the content to replace with
     */
    private function prepareHtmlData()
    {
        $getParams = $this->container->get(GetParams::class);
        $language  = $this->container->get(Language::class);
        $htmlMaker = $this->container->get(HtmlMaker::class);
        $bibleXML  = $this->container->get(BibleXML::class);

        $comboboxL = $language->getLanguageComboList($getParams->getLabelLang());
        $comboboxT = $language->getLanguageComboList($getParams->getContentLang());
        $comboboxB = $bibleXML->getBibleComboList();
        $comboboxY = [
            [
                'value' => 'mass',
                'sel'   => !$getParams->isRosary(),
                'text'  => $language->repls('@{heading}'),
            ],
            [
                'value' => 'rosary',
                'sel'   => $getParams->isRosary(),
                'text'  => $language->repls('@{rosary}'),
            ],
        ];

        $links = [
            [
                'label' => $language->repls('@{license}'),
                'text'  => 'MIT license',
                'url'   => 'https://opensource.org/licenses/MIT',
            ],
            [
                'label' => $language->repls('@{source}'),
                'text'  => 'Repository at GitHub.com'.Helper::showCommit(),
                'url'   => 'https://github.com/tommander/catholic-mass',
            ],
            [
                'label' => $language->repls('@{author}'),
                'text'  => 'Tomáš <q>Tommander</q> Rajnoha',
                'url'   => 'mailto:tommander@tommander.cz',
            ],
            'space',
            [
                'label'       => $language->repls('@{headerimg}'),
                'text'        => 'Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso',
                'url'         => 'https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg',
                'licensetext' => 'CC BY-SA 4.0',
                'licenseurl'  => 'https://creativecommons.org/licenses/by-sa/4.0',
            ],
            [
                'label'       => $language->repls('@{icons}'),
                'text'        => 'Font Awesome Free 5.15.3 by @fontawesome',
                'url'         => 'https://fontawesome.com',
                'licensetext' => 'Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License',
                'licenseurl'  => 'https://fontawesome.com/license/free',
            ],
            [
                'label'       => $language->repls('@{font}'),
                'text'        => 'Source Sans Pro by Paul D. Hunt',
                'url'         => 'https://fonts.google.com/specimen/Source+Sans+Pro',
                'licensetext' => 'Open Fonts License',
                'licenseurl'  => 'https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL',
            ],
            [
                'label' => $language->repls('@{texts}'),
                'list'  => $this->container->get(Language::class)->getLanguageData('', ''),
            ],
        ];

        $title = '@{heading}';
        if ($getParams->isRosary() === true) {
            $title = '@{rosary}';
        }

        $dateL = time();
        if ($getParams->isRosary() !== true) {
            $dateL = Helper::nextSunday(time());
        }

        $dateR = '@my{'.Helper::todaysMystery(time()).'}';
        if ($getParams->isRosary() !== true) {
            $lectionary = $this->container->get(Lectionary::class);
            $dateR      = '@su{'.$lectionary->sundayLabel(time()).'}';
        }

        // phpcs:disable
        /**
         * @psalm-suppress InvalidArgument
         */
        // phpcs:enable
        return [
            '/@@BASEURL@@/' => $this->baseurl,
            '/@@LANG@@/'    => $language->repls('@{html}'),
            '/@@TITLE@@/'   => $language->repls($title),
            '/@@IDXL@@/'    => $language->repls('@{idxL}'),
            '/@@IDXY@@/'    => $language->repls('@{idxY}'),
            '/@@IDXB@@/'    => $language->repls('@{idxB}'),
            '/@@IDXT@@/'    => $language->repls('@{idxT}'),
            '/@@CBL@@/'     => $htmlMaker->comboBoxContent($comboboxL, true),
            '/@@CBY@@/'     => $htmlMaker->comboBoxContent($comboboxY, true),
            '/@@CBB@@/'     => $htmlMaker->comboBoxContent($comboboxB, false),
            '/@@CBT@@/'     => $htmlMaker->comboBoxContent($comboboxT, true),
            '/@@LEGP@@/'    => $language->repls('@{lblP}'),
            '/@@LEGA@@/'    => $language->repls('@{lblA}'),
            '/@@LEGR@@/'    => $language->repls('@{lblR}'),
            '/@@DATEL@@/'   => date('d.m.Y', $dateL),
            '/@@DATER@@/'   => $language->repls($dateR),
            '/@@MAIN@@/'    => $htmlMaker->htmlObj($language->getContent($getParams->getType())),
            '/@@LINKS@@/'   => $htmlMaker->linksContent($links),
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
        $getParams = $this->container->get(GetParams::class);
        if ($getParams->isBuilder() === true) {
            $bibleIndexer = $this->container->get(BibleIndexer::class);
            $bibleXml     = $this->container->get(BibleXML::class);
            echo $bibleXml->buildIndices($bibleIndexer);
            return;
        }

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
