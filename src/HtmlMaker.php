<?php
/**
 * HTMLMaker unit
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\OrderOfMass;

use TMD\OrderOfMass\Models\{LanglistModel,LanglabelsModel};

if (defined('OOM_BASE') !== true) {
    die('This file cannot be viewed independently.');
}

/**
 * Building resulting HTML.
 */
class HtmlMaker
{

    /**
     * Logger service
     *
     * @var Logger
     */
    private $logger;

    /**
     * Language service
     *
     * @var Language
     */
    private $language;

    /**
     * Lectionary service
     *
     * @var Lectionary
     */
    private $lectionary;

    /**
     * Hello
     *
     * @var LanglistModel
     */
    private $langListModel;

    /**
     * Hello
     *
     * @var LanglabelsModel
     */
    private $langLabelsModel;


    /**
     * Saves service instances
     *
     * @param Logger          $logger          Logger
     * @param Language        $language        Language
     * @param Lectionary      $lectionary      Lectionary
     * @param LanglistModel   $langListModel   Langlist model
     * @param LanglabelsModel $langLabelsModel Langlabels model
     */
    public function __construct(Logger $logger, Language $language, Lectionary $lectionary, LanglistModel $langListModel, LanglabelsModel $langLabelsModel)
    {
        $this->logger          = $logger;
        $this->language        = $language;
        $this->lectionary      = $lectionary;
        $this->langListModel   = $langListModel;
        $this->langLabelsModel = $langLabelsModel;

    }//end __construct()


    /**
     * Creates a content of `select` HTML tag from an array
     *
     * @param array $def    Array defining the content of the combobox
     * @param bool  $simple Whether `optgroup` tags are being used
     *
     * @return string
     */
    public function comboBoxContent(array $def, bool $simple): string
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
                $ret .= sprintf("<optgroup label=\"%s\">\r\n", $grp);
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
    public function linksContent(array $def): string
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

            $ret .= sprintf(
                "<span>%s: <a href=\"%s\">%s</a></span>\r\n",
                $one['label'],
                $one['url'],
                $one['text'],
            );
        }//end foreach

        $langList = $this->langListModel->listLanguages();
        foreach ($langList as $langCode) {
            $langTitle  = $this->langListModel->getLanguageName($langCode);
            $langAuthor = $this->langListModel->getLanguageAuthor($langCode);
            $langLinks  = $this->langListModel->getLanguageLinks($langCode);

            $title = '';
            if ($langTitle !== null) {
                $title = $langTitle;
            }

            $author = '';
            if ($langAuthor !== null && $langAuthor !== 'Tommander') {
                $author = ' by '.$langAuthor;
            }

            $links = '';
            $cnt   = 1;
            if ($langLinks !== null) {
                foreach ($langLinks as $lnk) {
                    if ($cnt > 1) {
                        $links .= ', ';
                    }

                    $links .= sprintf(
                        "<a href=\"%s\">source %d</a>",
                        $lnk,
                        $cnt++
                    );
                }
            }

            if ($links !== '') {
                $links = " (${links})";
            }

            $ret .= sprintf(
                "<span>%s%s%s</span>\r\n",
                $title,
                $author,
                $links
            );
        }//end foreach

        return $ret;

    }//end linksContent()


    /**
     * Convert original JSON object to an HTML representation
     *
     * @param object $obj JSON object
     *
     * @return string
     */
    public function objToHtml(object $obj): string
    {
        $who  = '';
        $what = '';
        $cls  = '';

        if (isset($obj->reading) === true) {
            $what = "<a href=\"".$this->langLabelsModel->getLabel('dbrlink')."\">".$this->language->repls('@icon{booklink} @{dbrtext}')."</a>";
        } else if (isset($obj->{""}) === true) {
            $what = $this->language->repls($obj->{""}, true);
        } else if (isset($obj->{"p"}) === true) {
            $who  = "<span class=\"who\">P:</span>";
            $what = $this->language->repls($obj->{"p"}, true);
        } else if (isset($obj->{"a"}) === true) {
            $who  = "<span class=\"who\">A:</span>";
            $what = "<strong>".$this->language->repls($obj->{"a"}, true)."</strong>";
        } else if (isset($obj->{"r"}) === true) {
            $who  = "<span class=\"who\">R:</span>";
            $what = $this->language->repls($obj->{"r"}, true);
        } else if (isset($obj->{"read"}) === true) {
            $who   = "<span class=\"who\">R:</span>";
            $what1 = "@icon{bible}";
            $what2 = '';
            switch ($obj->{"read"}) {
            case 'r1':
                $what2 = '@{read1} ';
                break;
            case 'r2':
                $what2 = '@{read2} ';
                break;
            case 'p':
                $what2 = '@{psalm} ';
                break;
            case 'a':
                $what2 = '@{alleluia} ';
                break;
            case 'g':
                $what2 = '@{readE} ';
            }

            $what3 = '';
            $reads = $this->lectionary->getReadings(time());
            if ($reads !== null) {
                $what3raw = '';
                if (array_key_exists($obj->{"read"}, $reads) === true) {
                    $what3raw = $reads[$obj->{"read"}];
                }

                if (is_string($what3raw) === true) {
                    $what3 = $this->language->replbb($what3raw);
                } else if (is_array($what3raw) === true) {
                    foreach ($what3raw as $what3one) {
                        $what3 .= $this->language->replbb($what3one);
                    }
                }
            }

            $what = $this->language->repls($what1.' '.$what2.' '.$what3, true);
        }//end if

        if ($who === '') {
            $cls = ' class="command"';
        }

        return "<div${cls}>${who}<span class=\"what\">${what}</span></div>\r\n";

    }//end objToHtml()


    /**
     * Converts a JSON array to HTML (the result is a row of tabs each with their content)
     *
     * @param array $arr JSON array
     *
     * @return string
     */
    public function arrToHtml(array $arr): string
    {
        $ret = "<div class=\"choice\">\r\n";

        $ret .= "  <div class=\"choiceTabs\">\r\n";
        $ret .= "    <div class=\"choiceTabsLabel\"><span class=\"command\"><i class=\"fa-regular fa-hand-pointer\"></i> ".$this->langLabelsModel->getLabel('choose').":</span></div>\r\n";
        $i    = 0;
        $cont = '';
        foreach ($arr as $item) {
            $i++;

            if (is_object($item) !== true) {
                continue;
            }

            $addTab  = '';
            $addCont = ' hidden="hidden"';
            if ($i === 1) {
                $addTab  = ' optionSelected';
                $addCont = '';
            }

            $ret .= "    <div class=\"option option${i}${addTab}\" onclick=\"tabClick(this)\">";
            if (isset($item->title) === true && $item->title !== '') {
                $ret .= $this->language->repls($item->title);
            } else {
                $ret .= $i;
            }

            $ret .= "</div>\r\n";

            $cont .= "    <div class=\"option option${i}\"${addCont}>\r\n";
            if (isset($item->content) === true && is_array($item->content) === true) {
                foreach ($item->content as $subitem) {
                    if (is_object($subitem) === true) {
                        $cont .= $this->objToHtml($subitem);
                    }
                }
            }

            $cont .= "    </div>\r\n";
        }//end foreach

        $ret .= "  </div>\r\n";

        $ret .= "  <div class=\"choiceContent\">\r\n";
        $ret .= $cont;
        $ret .= "  </div>\r\n";
        $ret .= "</div>\r\n";

        return $ret;

    }//end arrToHtml()


    /**
     * Returns complete mass/rosary HTML content
     *
     * @param array $contentArray Hello
     *
     * @return string
     */
    public function htmlObj(array $contentArray): string
    {
        $ret = '';
        foreach ($contentArray as $row) {
            if (is_object($row) === true) {
                $ret .= $this->objToHtml($row);
            } else if (is_array($row) === true) {
                $ret .= $this->arrToHtml($row);
            }
        }

        return $ret;

    }//end htmlObj()


}//end class
