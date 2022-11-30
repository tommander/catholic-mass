<?php
/**
 * Main class of the Order of Mass app
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
     * Creates a container
     */
    public function __construct()
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);
        $containerBuilder->addDefinitions(
            [
                Logger::class         => \DI\create(Logger::class)->lazy(),
                Config::class         => \DI\create(Config::class)->constructor(\DI\get(Logger::class))->lazy(),
                GetParams::class      => \DI\create(GetParams::class)->constructor(\DI\get(Logger::class))->lazy(),
                CsrfProtection::class => \DI\create(CsrfProtection::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(Encryption::class),
                )->lazy(),
                Encryption::class     => \DI\create(Encryption::class)->constructor(\DI\get(Logger::class))->lazy(),
                Feedback::class       => \DI\create(Feedback::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(Encryption::class),
                    \DI\get(CsrfProtection::class),
                )->lazy(),
                BibleReader::class    => \DI\create(BibleReader::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(GetParams::class),
                    \DI\get(Language::class),
                )->lazy(),
                Lectionary::class     => \DI\create(Lectionary::class)->constructor(\DI\get(Logger::class))->lazy(),
                Measure::class        => \DI\create(Measure::class)->constructor(\DI\get(Logger::class))->lazy(),
                HtmlMaker::class      => \DI\create(HtmlMaker::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(Language::class),
                    \DI\get(Lectionary::class),
                )->lazy(),
                Language::class       => \DI\create(Language::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(GetParams::class),
                    \DI\get(BibleReader::class),
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
        $config    = $this->container->get(Config::class);
        $getParams = $this->container->get(GetParams::class);
        $language  = $this->container->get(Language::class);
        $htmlMaker = $this->container->get(HtmlMaker::class);
        $bibleRead = $this->container->get(BibleReader::class);

        $comboboxL = $language->getLanguageComboList($getParams->getLabelLang());
        $comboboxT = $language->getLanguageComboList($getParams->getContentLang());
        $comboboxB = $bibleRead->getBibleList();
        $comboboxY = [
            [
                'value' => 'mass',
                'sel'   => (!$getParams->isRosary() && !$getParams->isBible()),
                'text'  => $language->repls('@{heading}'),
            ],
            [
                'value' => 'rosary',
                'sel'   => $getParams->isRosary(),
                'text'  => $language->repls('@{rosary}'),
            ],
            [
                'value' => 'bible',
                'sel'   => $getParams->isBible(),
                'text'  => $language->repls('@{bible}'),
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
                'text'        => 'Font Awesome Free 6 by @fontawesome',
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
        } else if ($getParams->isBible() === true) {
            $title = '@{bible}';
        }

        $dateL = time();
        if ($getParams->isRosary() !== true && $getParams->isBible() !== true) {
            $dateL = Helper::nextSunday(time());
        }

        $dateR = '';
        if ($getParams->isRosary() === true) {
            $dateR = '@my{'.Helper::todaysMystery(time()).'}';
        } else if ($getParams->isRosary() !== true && $getParams->isBible() !== true) {
            $lectionary = $this->container->get(Lectionary::class);
            $dateR      = '@su{'.$lectionary->sundayLabel(time()).'}';
        }

        $mainType = 'mass';
        if ($getParams->isRosary() === true) {
            $mainType = 'rosary';
        } else if ($getParams->isBible() === true) {
            $mainType = 'bible';
        }

        $htmlContent = '';
        if ($getParams->isBible() === true) {
            $htmlContent = $this->container->get(BibleReader::class)->renderBible();
        } else {
            $tempCont = $language->getContent($getParams->getType());
            if (is_array($tempCont) === true) {
                $htmlContent = $htmlMaker->htmlObj($tempCont);
            }
        }

        $csrfProtection = $this->container->get(CsrfProtection::class);
        $csrfToken      = $csrfProtection->generateCsrf();

        // phpcs:disable
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress UndefinedConstant
         */
        // phpcs:enable
        return [
            '/@@BASEURL@@/'   => BASE_URL,
            '/@@LANG@@/'      => $language->repls('@{html}'),
            '/@@TITLE@@/'     => $language->repls($title),
            '/@@IDXL@@/'      => $language->repls('@{idxL}'),
            '/@@IDXY@@/'      => $language->repls('@{idxY}'),
            '/@@IDXB@@/'      => $language->repls('@{idxB}'),
            '/@@IDXT@@/'      => $language->repls('@{idxT}'),
            '/@@CBL@@/'       => $htmlMaker->comboBoxContent($comboboxL, true),
            '/@@CBY@@/'       => $htmlMaker->comboBoxContent($comboboxY, true),
            '/@@CBB@@/'       => $htmlMaker->comboBoxContent($comboboxB, false),
            '/@@CBT@@/'       => $htmlMaker->comboBoxContent($comboboxT, true),
            '/@@LEGP@@/'      => $language->repls('@{lblP}'),
            '/@@LEGA@@/'      => $language->repls('@{lblA}'),
            '/@@LEGR@@/'      => $language->repls('@{lblR}'),
            '/@@DATEL@@/'     => date('d.m.Y', $dateL),
            '/@@DATER@@/'     => $language->repls($dateR),
            '/@@MAIN@@/'      => $htmlContent,
            '/@@MAINTYPE@@/'  => $mainType,
            '/@@LINKS@@/'     => $htmlMaker->linksContent($links),
            '/@@MEMPEAK@@/'   => \memory_get_peak_usage(true),
            '/@@MEMUSE@@/'    => \memory_get_usage(true),
            '/@@CSRFTOKEN@@/' => $csrfToken,
        ];

    }//end prepareHtmlData()


    /**
     * Runs the app (builds a final HTML)
     *
     * @return void
     */
    public function run()
    {
        $config = $this->container->get(Config::class);
        $env    = $config->getEnvironment();
        $meas   = null;
        if ($env === 'development') {
            $meas = $this->container->get(Measure::class);
            $meas->start();
        }

        $getParams = $this->container->get(GetParams::class);
        $feedback  = $this->container->get(Feedback::class);
        if ($getParams->isFeedbackWrite() === true) {
            $feedback->saveFeedback();
            return;
        }

        if ($getParams->isFeedbackRead() === true) {
            $feedback->readFeedback();
            return;
        }

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
        if ($env === 'development' && $meas !== null) {
            echo "<!-- \r\n";
            var_export($meas->finish());
            echo "\r\n -->\r\n";
        }

    }//end run()


}//end class
