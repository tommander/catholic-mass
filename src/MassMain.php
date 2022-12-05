<?php
/**
 * Main class of the Order of Mass app
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

use TMD\OrderOfMass\Models\{BibleindexModel,BiblejsonModel,BiblemapModel,BooklistModel,Iso6393listModel,LangcontentModel,LanglabelsModel,LanglistModel,LectionaryModel,LectlistModel};

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
                BibleindexModel::class  => \DI\create(BibleindexModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                BiblejsonModel::class   => \DI\create(BiblejsonModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                BiblemapModel::class    => \DI\create(BiblemapModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                BooklistModel::class    => \DI\create(BooklistModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                Iso6393listModel::class => \DI\create(Iso6393listModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                LangcontentModel::class => \DI\create(LangcontentModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                LanglabelsModel::class  => \DI\create(LanglabelsModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                LanglistModel::class    => \DI\create(LanglistModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                LectionaryModel::class  => \DI\create(LectionaryModel::class)->constructor(\DI\get(Logger::class))->lazy(),
                LectlistModel::class    => \DI\create(LectlistModel::class)->constructor(\DI\get(Logger::class))->lazy(),

                BibleReader::class      => \DI\create(BibleReader::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(GetParams::class),
                    \DI\get(Language::class),
                    \DI\get(BibleindexModel::class),
                    \DI\get(BiblejsonModel::class),
                    \DI\get(BiblemapModel::class),
                    \DI\get(BooklistModel::class),
                    \DI\get(LanglabelsModel::class),
                )->lazy(),
                Config::class           => \DI\create(Config::class)->constructor(\DI\get(Logger::class))->lazy(),
                GetParams::class        => \DI\create(GetParams::class)->constructor(\DI\get(Logger::class))->lazy(),
                HtmlMaker::class        => \DI\create(HtmlMaker::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(Language::class),
                    \DI\get(Lectionary::class),
                    \DI\get(LanglistModel::class),
                    \DI\get(LanglabelsModel::class),
                )->lazy(),
                Language::class         => \DI\create(Language::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(GetParams::class),
                    \DI\get(BibleReader::class),
                    \DI\get(Lectionary::class),
                    \DI\get(BooklistModel::class),
                    \DI\get(LanglistModel::class),
                    \DI\get(LangcontentModel::class),
                    \DI\get(LanglabelsModel::class),
                    \DI\get(BiblemapModel::class),
                )->lazy(),
                Lectionary::class       => \DI\create(Lectionary::class)->constructor(
                    \DI\get(Logger::class),
                    \DI\get(LectionaryModel::class),
                    \DI\get(LectlistModel::class),
                )->lazy(),
                Logger::class           => \DI\create(Logger::class)->lazy(),
                Measure::class          => \DI\create(Measure::class)->constructor(\DI\get(Logger::class))->lazy(),
            ]
        );
        $this->container = $containerBuilder->build();

        $getParams       = $this->container->get(GetParams::class);
        $bibleIndexModel = $this->container->get(BibleindexModel::class);

        $bibleFile  = '';
        $bibleParam = $getParams->getParam(GetParams::PARAM_BIBLE);
        if ($bibleParam !== '') {
            $params = explode('|', $bibleParam);
            if (count($params) === 2) {
                $file = $bibleIndexModel->getBibleFile($params[0], $params[1]);
                if ($file !== null) {
                    $bibleFile = $file;
                }
            }
        }

        $bibleJsonModel = $this->container->get(BiblejsonModel::class);
        $bibleJsonModel->load($bibleFile);

        $bibleMapModel = $this->container->get(BiblemapModel::class);
        $bibleMapModel->load($bibleFile);

        $langContentModel = $this->container->get(LangcontentModel::class);
        if ($langContentModel->load($getParams->getParam(GetParams::PARAM_TEXTS)) !== true) {
            throw new \Exception('Langcontent load failed');
        }

        $langLabelsModel = $this->container->get(LanglabelsModel::class);
        if ($langLabelsModel->load($getParams->getParam(GetParams::PARAM_LABELS)) !== true) {
            throw new \Exception('Langlabels load failed');
        }

        $lectionaryModel = $this->container->get(LectionaryModel::class);
        $lectionaryModel->load(intval(date('Y')));

    }//end __construct()


    /**
     * Prepares an associative array for replacement of content placeholders in an HTML template
     *
     * @return array<string, mixed> Key is a replacement placeholder, value is the content to replace with
     */
    private function prepareHtmlData()
    {
        $config     = $this->container->get(Config::class);
        $getParams  = $this->container->get(GetParams::class);
        $language   = $this->container->get(Language::class);
        $htmlMaker  = $this->container->get(HtmlMaker::class);
        $bibleRead  = $this->container->get(BibleReader::class);
        $lectionary = $this->container->get(Lectionary::class);

        $bibleIndexModel  = $this->container->get(BibleindexModel::class);
        $langListModel    = $this->container->get(LanglistModel::class);
        $langContentModel = $this->container->get(LangcontentModel::class);
        $langLabelsModel  = $this->container->get(LanglabelsModel::class);

        $comboboxL = $langListModel->listLanguagesForSelect($getParams->getParam(GetParams::PARAM_LABELS));
        $comboboxT = $langListModel->listLanguagesForSelect($getParams->getParam(GetParams::PARAM_TEXTS));
        $comboboxB = $bibleIndexModel->listBiblesForSelect(
            $getParams->getParam(GetParams::PARAM_LABELS),
            $getParams->getParam(GetParams::PARAM_TEXTS),
            $getParams->getParam(GetParams::PARAM_BIBLE)
        );
        $comboboxY = [
            [
                'value' => 'mass',
                'sel'   => ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_MASS),
                'text'  => $langLabelsModel->getLabel('heading'),
            ],
            [
                'value' => 'rosary',
                'sel'   => ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_ROSARY),
                'text'  => $langLabelsModel->getLabel('rosary'),
            ],
            [
                'value' => 'bible',
                'sel'   => ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_BIBLE),
                'text'  => $langLabelsModel->getLabel('bible'),
            ],
        ];

        $links = [
            [
                'label' => $langLabelsModel->getLabel('license'),
                'text'  => 'MIT license',
                'url'   => 'https://opensource.org/licenses/MIT',
            ],
            [
                'label' => $langLabelsModel->getLabel('source'),
                'text'  => 'Repository at GitHub.com'.Helper::showCommit(),
                'url'   => 'https://github.com/tommander/catholic-mass',
            ],
            [
                'label' => $langLabelsModel->getLabel('author'),
                'text'  => 'Tomáš <q>Tommander</q> Rajnoha',
                'url'   => 'mailto:tommander@tommander.cz',
            ],
            'space',
            [
                'label'       => $langLabelsModel->getLabel('headerimg'),
                'text'        => 'Iglesia de San Carlos Borromeo, Viena, Austria by Diego Delso',
                'url'         => 'https://commons.wikimedia.org/wiki/File:Iglesia_de_San_Carlos_Borromeo,_Viena,_Austria,_2020-01-31,_DD_164-166_HDR.jpg',
                'licensetext' => 'CC BY-SA 4.0',
                'licenseurl'  => 'https://creativecommons.org/licenses/by-sa/4.0',
            ],
            [
                'label'       => $langLabelsModel->getLabel('icons'),
                'text'        => 'Font Awesome Free 6 by @fontawesome',
                'url'         => 'https://fontawesome.com',
                'licensetext' => 'Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License',
                'licenseurl'  => 'https://fontawesome.com/license/free',
            ],
            [
                'label'       => $langLabelsModel->getLabel('font'),
                'text'        => 'Source Sans Pro by Paul D. Hunt',
                'url'         => 'https://fonts.google.com/specimen/Source+Sans+Pro',
                'licensetext' => 'Open Fonts License',
                'licenseurl'  => 'https://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL',
            ],
        ];

        $title = 'heading';
        if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_ROSARY) {
            $title = 'rosary';
        } else if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_BIBLE) {
            $title = 'bible';
        }

        $dateL = time();
        if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_MASS) {
            $dateL = Helper::nextSunday(time());
        }

        $dateR = '';
        if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_ROSARY) {
            $dateR = $langLabelsModel->getMystery(Helper::todaysMystery(time()));
        } else if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_MASS) {
            $sundayLabel = $lectionary->sundayLabel(time());
            if ($sundayLabel !== null) {
                $dateR = $langLabelsModel->getSunday($sundayLabel);
            }
        }

        $htmlContent = '';
        if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_BIBLE) {
            $htmlContent = $bibleRead->renderBible();
        } else {
            $tempCont = [];
            if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_ROSARY) {
                $tempTempCont = $langContentModel->getRosary();
                if ($tempTempCont !== null) {
                    $tempCont = $tempTempCont;
                }
            }

            if ($getParams->getParam(GetParams::PARAM_TYPE) === GetParams::TYPE_MASS) {
                $tempTempCont = $langContentModel->getMass();
                if ($tempTempCont !== null) {
                    $tempCont = $tempTempCont;
                }
            }

            if (is_array($tempCont) === true) {
                $htmlContent = $htmlMaker->htmlObj($tempCont);
            }
        }//end if

        // phpcs:disable
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress UndefinedConstant
         */
        // phpcs:enable
        return [
            '/@@BASEURL@@/'  => BASE_URL,
            '/@@LANG@@/'     => $langLabelsModel->getLabel('html'),
            '/@@TITLE@@/'    => $langLabelsModel->getLabel($title),
            '/@@IDXL@@/'     => $langLabelsModel->getLabel('idxL'),
            '/@@IDXY@@/'     => $langLabelsModel->getLabel('idxY'),
            '/@@IDXB@@/'     => $langLabelsModel->getLabel('idxB'),
            '/@@IDXT@@/'     => $langLabelsModel->getLabel('idxT'),
            '/@@FLDL@@/'     => GetParams::PARAM_LABELS,
            '/@@FLDY@@/'     => GetParams::PARAM_TYPE,
            '/@@FLDB@@/'     => GetParams::PARAM_BIBLE,
            '/@@FLDT@@/'     => GetParams::PARAM_TEXTS,
            '/@@CBL@@/'      => $htmlMaker->comboBoxContent($comboboxL, true),
            '/@@CBY@@/'      => $htmlMaker->comboBoxContent($comboboxY, true),
            '/@@CBB@@/'      => $htmlMaker->comboBoxContent($comboboxB, false),
            '/@@CBT@@/'      => $htmlMaker->comboBoxContent($comboboxT, true),
            '/@@LEGP@@/'     => $langLabelsModel->getLabel('lblP'),
            '/@@LEGA@@/'     => $langLabelsModel->getLabel('lblA'),
            '/@@LEGR@@/'     => $langLabelsModel->getLabel('lblR'),
            '/@@DATEL@@/'    => date('d.m.Y', $dateL),
            '/@@DATER@@/'    => $dateR,
            '/@@MAIN@@/'     => $htmlContent,
            '/@@MAINTYPE@@/' => $getParams->getParam(GetParams::PARAM_TYPE),
            '/@@LINKS@@/'    => $htmlMaker->linksContent($links),
            '/@@MEMPEAK@@/'  => \memory_get_peak_usage(true),
            '/@@MEMUSE@@/'   => \memory_get_usage(true),
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
