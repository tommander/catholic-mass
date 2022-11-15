<?php
/**
 * HTMLMaker unit
 *
 * PHP version 7.4
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
 * Building resulting HTML.
 */
class HtmlMaker
{

    /**
     * Hello
     *
     * @var Logger
     */
    private $logger;

    /**
     * Hello
     *
     * @var Language
     */
    private $language;


    /**
     * Saves the instance of Logger
     *
     * @param Logger   $logger   Logger
     * @param Language $language Language
     */
    public function __construct(Logger $logger, Language $language)
    {
        $this->logger   = $logger;
        $this->language = $language;

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
            $what = "<a href=\"".$this->language->repls('@{dbrlink}')."\">".$this->language->repls('@icon{booklink} @{dbrtext}')."</a>";
        } else if (isset($obj->{""}) === true) {
            $what = $this->language->repl($obj->{""});
        } else if (isset($obj->{"p"}) === true) {
            $who  = "<span class=\"who\">P:</span>";
            $what = $this->language->repl($obj->{"p"});
        } else if (isset($obj->{"a"}) === true) {
            $who  = "<span class=\"who\">A:</span>";
            $what = "<strong>".$this->language->repl($obj->{"a"})."</strong>";
        } else if (isset($obj->{"r"}) === true) {
            $who  = "<span class=\"who\">R:</span>";
            $what = $this->language->repl($obj->{"r"});
        }

        if ($who === '') {
            $cls = ' class="command"';
        }

        return "<div${cls}>${who}<span class=\"what\">${what}</span></div>\r\n";

    }//end objToHtml()


    /**
     * Short description
     *
     * @param array $arr Array
     *
     * @return string
     */
    public function arrToHtml(array $arr): string
    {
        $ret = "<div class=\"choice\">\r\n";

        $ret .= "  <div class=\"choiceTabs\">\r\n";
        $ret .= "    <div class=\"choiceTabsLabel\"><span class=\"command\"><i class=\"fa-regular fa-hand-pointer\"></i> ".$this->language->repls('@{choose}').":</span></div>\r\n";
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
            return $this->parseToHtml($this->language->replre($row->read));
        } else if (is_object($row) === true) {
            return $this->objToHtml($row);
        } else if (is_array($row) === true) {
            return $this->arrToHtml($row);
        }

        return '';

    }//end parseToHtml()


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
            $ret .= $this->parseToHtml($row);
        }

        return $ret;

    }//end htmlObj()


}//end class
