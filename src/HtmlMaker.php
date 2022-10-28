<?php
/**
 * HTMLMaker unit
 *
 * PHP version 7.4
 *
 * @package OrderOfMass
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
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
    public function htmlObj(array $contentArray): string
    {
/*        $section = 'mass';
        if ($this->isRosary() === true) {
            $section = 'rosary';
        }

        if (isset($this->contentJson->{$section}) === false || is_array($this->contentJson->{$section}) === false) {
            return var_export($this->contentJson, true);
        }*/

        $ret = '';
        foreach ($contentArray as $row) {
            $ret .= $this->parseToHtml($row);
        }

        return $ret;

    }//end htmlObj()
}//end class
