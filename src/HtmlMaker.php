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
use TMD\OrderOfMass\Exceptions\{OomException,ModelException};

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
     * Hello
     *
     * @var GetParams
     */
    private $getParams;


    /**
     * Saves service instances
     *
     * @param Logger          $logger          Logger service
     * @param Language        $language        Language service
     * @param Lectionary      $lectionary      Lectionary service
     * @param LanglistModel   $langListModel   Langlist model
     * @param LanglabelsModel $langLabelsModel Langlabels model
     * @param GetParams       $getParams       GetParams service
     */
    public function __construct(Logger $logger, Language $language, Lectionary $lectionary, LanglistModel $langListModel, LanglabelsModel $langLabelsModel, GetParams $getParams)
    {
        $this->logger          = $logger;
        $this->language        = $language;
        $this->lectionary      = $lectionary;
        $this->langListModel   = $langListModel;
        $this->langLabelsModel = $langLabelsModel;
        $this->getParams       = $getParams;

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
                if (is_array($opt) !== true) {
                    throw new OomException('Incorrect array structure (item is not array)');
                }

                if (array_key_exists('value', $opt) !== true
                    || array_key_exists('sel', $opt) !== true
                    || array_key_exists('text', $opt) !== true
                ) {
                    throw new OomException('Incorrect array structure (array does not contain necessary keys)');
                }

                $sel = '';
                if ($opt['sel'] === true) {
                    $sel = ' selected="selected"';
                }

                $ret .= sprintf("<option value=\"%s\"%s>%s</option>", $opt['value'], $sel, $opt['text']);
            }

            return $ret;
        }//end if

        foreach ($def as $grp => $lst) {
            if (is_array($lst) !== true) {
                throw new OomException('Incorrect array structure (group list is not array)');
            }

            if ($grp !== '') {
                $ret .= sprintf("<optgroup label=\"%s\">", $grp);
            }

            foreach ($lst as $opt) {
                if (is_array($opt) !== true) {
                    throw new OomException('Incorrect array structure (group list item is not array)');
                }

                if (array_key_exists('value', $opt) !== true
                    || array_key_exists('sel', $opt) !== true
                    || array_key_exists('text', $opt) !== true
                ) {
                    throw new OomException('Incorrect array structure (group list array does not contain necessary keys)');
                }

                $sel = '';
                if ($opt['sel'] === true) {
                    $sel = ' selected="selected"';
                }

                $ret .= sprintf("<option value=\"%s\"%s>%s</option>", $opt['value'], $sel, $opt['text']);
            }

            if ($grp !== '') {
                $ret .= "</optgroup>";
            }
        }//end foreach

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
                throw new OomException('Unexpected array structure');
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
            $title      = $this->langListModel->getLanguageName($langCode);
            $langAuthor = $this->langListModel->getLanguageAuthor($langCode);
            $langLinks  = $this->langListModel->getLanguageLinks($langCode);

            $author = '';
            if ($langAuthor !== 'Tommander') {
                $author = ' by '.$langAuthor;
            }

            $links = '';
            $cnt   = 1;
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
     * This is a small helper function for {@see HtmlMaker::objectAllowed()} that takes the `allowed` or `forbid`
     * property of a JSON object and returns true, if the given date is found in the rules
     *
     * @param object $objectLimits Forbid/Allow property of the JSON object
     * @param int    $time         Timestamp
     *
     * @return bool
     */
    private function objectLimitHit(object $objectLimits, int $time): bool
    {
        if (property_exists($objectLimits, 'weekday') === true && is_array($objectLimits->weekday) === true && in_array(date('D', $time), $objectLimits->weekday) === true) {
            return true;
        }

        if (property_exists($objectLimits, 'day') === true && is_array($objectLimits->day) === true && in_array($this->lectionary->sundayLabel($time), $objectLimits->day) === true) {
            return true;
        }

        return false;

    }//end objectLimitHit()


    /**
     * This function is used to check a JSON object. If it contains properties `allow` or `forbid`, it means
     * the object can be displayed only under certain circumstances.
     *
     * @param object $obj  Object to check
     * @param int    $time Timestamp
     *
     * @return bool
     */
    private function objectAllowed(object $obj, int $time): bool
    {
        // No limitations => allowed.
        if (property_exists($obj, 'allow') !== true && property_exists($obj, 'forbid') !== true) {
            return true;
        }

        $this->logger->debug("Object is limited.\r\n".var_export($obj, true));

        // Forbid and allow.
        if (property_exists($obj, 'allow') === true && property_exists($obj, 'forbid') === true) {
            $ret = ($this->objectLimitHit($obj->forbid, $time) === false && $this->objectLimitHit($obj->allow, $time) === true);
            $this->logger->debug('AllowForbid hit: '.var_export($ret, true));
            return $ret;
        }

        // Forbid only.
        if (property_exists($obj, 'forbid') === true) {
            $ret = ($this->objectLimitHit($obj->forbid, $time) === false);
            $this->logger->debug('Forbid hit: '.var_export($ret, true));
            return $ret;
        }

        // Allow only.
        if (property_exists($obj, 'allow') === true) {
            $ret = ($this->objectLimitHit($obj->allow, $time) === true);
            $this->logger->debug('Allow hit: '.var_export($ret, true));
            return $ret;
        }

        throw new OomException('Program flow should never have gotten here.');

    }//end objectAllowed()


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

        if ($this->objectAllowed($obj, $this->getParams->getTimestamp()) !== true) {
            return '';
        }

        if (isset($obj->h1) === true) {
            $cls  = ' class="heading1"';
            $what = $this->language->repls($obj->h1);
        } else if (isset($obj->h2) === true) {
            $cls  = ' class="heading2"';
            $what = $this->language->repls($obj->h2);
        } else if (isset($obj->reading) === true) {
            $cls  = ' class="command"';
            $what = "<span class=\"what\"><a href=\"".$this->langLabelsModel->getLabel('dbrlink')."\">".$this->language->repls('@icon{booklink}')." ".$this->langLabelsModel->getLabel('dbrtext')."</a></span>";
        } else if (isset($obj->{""}) === true) {
            $cls  = ' class="command"';
            $what = "<span class=\"what\">".$this->language->repls($obj->{""}, true)."</span>";
        } else if (isset($obj->{"p"}) === true) {
            $who  = "<span class=\"who\">P:</span>";
            $what = "<span class=\"what\">".$this->language->repls($obj->{"p"}, true)."</span>";
        } else if (isset($obj->{"a"}) === true) {
            $who  = "<span class=\"who\">A:</span>";
            $what = "<span class=\"what\"><strong>".$this->language->repls($obj->{"a"}, true)."</strong></span>";
        } else if (isset($obj->{"r"}) === true) {
            $who  = "<span class=\"who\">R:</span>";
            $what = "<span class=\"what\">".$this->language->repls($obj->{"r"}, true)."</span>";
        } else if (\property_exists($obj, 'read') === true) {
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
                break;
            default:
                throw new OomException('Unknown reading code "'.$obj->{"read"}.'"');
            }

            $what3 = '';
            $reads = $this->lectionary->getReadings($this->getParams->getTimestamp());
            if ($reads !== null) {
                $what3raw = '';
                if (array_key_exists($obj->{"read"}, $reads) === true) {
                    $what3raw = $reads[$obj->{"read"}];
                }

                if (is_string($what3raw) === true && $what3raw !== '') {
                    $what3 = $this->language->replbb($what3raw);
                } else if (is_array($what3raw) === true) {
                    foreach ($what3raw as $what3one) {
                        if ($what3one !== '') {
                            $what3 .= $this->language->replbb($what3one);
                        }
                    }
                }
            }

            $what = "<span class=\"what\">".$this->language->repls($what1.' '.$what2.' '.$what3, true)."</span>";
        }//end if

        return "<div${cls}>${who}${what}</div>";

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
        $tabIDprefix = 'L'.Helper::hash(var_export($arr, true)).\bin2hex(\openssl_random_pseudo_bytes(2));

        // $ret  = "<div class=\"choice\"><div role=\"tablist\" aria-label=\"\" class=\"choiceTabs\">";
        $label = "<div class=\"choiceTabsLabel\"><span class=\"command\"><i class=\"fa-regular fa-hand-pointer\"></i> ".$this->langLabelsModel->getLabel('choose').":</span></div>";
        $ret   = "<div class=\"choice\"><div id=\"${tabIDprefix}-LIST\" role=\"tablist\" aria-label=\"\">$label";
        $i     = 0;
        $cont  = '';

        foreach ($arr as $item) {
            $i++;

            if (is_object($item) !== true) {
                throw new OomException('Array item is not an object');
            }

            if (property_exists($item, 'content') !== true) {
                throw new OomException('Item object does not have "content"');
            }

            if (is_array($item->content) !== true) {
                throw new OomException('Item object content is not an array');
            }

            if ($this->objectAllowed($item, $this->getParams->getTimestamp()) !== true) {
                return '';
            }

            $addTab  = 'false';
            $addCont = ' hidden="hidden"';
            $tabIdx  = '-1';
            $tabID   = $tabIDprefix.$i.'-TAB';
            $panelID = $tabIDprefix.$i.'-PANEL';
            if ($i === 1) {
                $addTab  = 'true';
                $addCont = '';
                $tabIdx  = '0';
            }

            $ret .= "<button role=\"tab\" aria-selected=\"${addTab}\" aria-controls=\"${panelID}\" id=\"${tabID}\" tabindex=\"${tabIdx}\">";
            if (isset($item->title) === true && $item->title !== '') {
                $ret .= $this->language->repls($item->title);
            } else {
                $ret .= $i;
            }

            $ret .= "</button>";

            $cont .= "<div role=\"tabpanel\" id=\"${panelID}\"${addCont} aria-labelledby=\"${tabID}\" tabIndex=\"-1\">";
            $cont .= $this->htmlObj($item->content);
            $cont .= "</div>";
        }//end foreach

        // $ret .= "</div><div class=\"choiceContent\">$cont</div></div>";
        $ret .= "$cont</div></div>";

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
            } else {
                throw new OomException('Content item is not array or object');
            }
        }

        return $ret;

    }//end htmlObj()


}//end class
