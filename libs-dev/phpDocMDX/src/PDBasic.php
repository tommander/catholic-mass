<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\PDMDX;

abstract class PDBasic
{


    /**
     * Creates a standard Markdown documentation object.
     *
     * ```php
     * $data = [
     *   'headingLevel' => 1,
     *   'objectType' => '',
     *   'objectName' => '',
     *   'props' => [
     *     '' => '',
     *   ],
     *   'docBlock' => null, //?PDDocBlock
     *   'sub' => [
     *     '' => [
     *       'type' => '', //str|obj|objstr
     *       'items' => [
     *         null, //string|?PDBasic
     *       ]
     *     ],
     *   ],
     * ];
     * ```
     *
     * @param array $data Object data
     *
     * @return string
     */
    protected function mdOutWithHeading(array $data): string
    {

        // Heading.
        $ret = sprintf(
            "%s %s <q>%s</q>\r\n\r\n",
            \str_repeat('#', intval($data['headingLevel'])),
            $data['objectType'],
            $data['objectName']
        );

        // Properties.
        if (isset($data['props']) === true && is_array($data['props']) === true && count($data['props']) > 0) {
            foreach ($data['props'] as $propKey => $propVal) {
                if (is_string($propVal) !== true || $propVal === '') {
                    continue;
                }

                if (strcasecmp($propKey, 'line') === 0
                    || strcasecmp($propKey, 'path') === 0
                ) {
                    $ret .= sprintf(
                        "**%s**: %s  \r\n",
                        $propKey,
                        $propVal
                    );
                } else {
                    $ret .= sprintf(
                        "**%s**: `%s`  \r\n",
                        $propKey,
                        $propVal
                    );
                }
            }//end foreach

            $ret .= "\r\n";
        }//end if

        // DocBlock.
        if (isset($data['docBlock']) === true && $data['docBlock'] !== null) {
            $ret .= $data['docBlock']->md();
        }

        // Subsections.
        if (isset($data['sub']) === true && is_array($data['sub']) === true && count($data['sub']) > 0) {
            foreach ($data['sub'] as $subName => $subVal) {
                if (is_array($subVal) !== true
                    || isset($subVal['type']) !== true
                    || in_array($subVal['type'], ['str', 'obj', 'objstr']) !== true
                    || isset($subVal['items']) !== true
                    || is_array($subVal['items']) !== true
                    || count($subVal['items']) === 0
                ) {
                    continue;
                }

                $ret .= sprintf(
                    "%s %s\r\n\r\n",
                    \str_repeat('#', (intval($data['headingLevel']) + 1)),
                    $subName
                );

                foreach ($subVal['items'] as $subItem) {
                    if ($subVal['type'] === 'str' && is_string($subItem) === true) {
                        $ret .= '- '.$subItem."  \r\n";
                    } else if ($subVal['type'] === 'obj' && is_object($subItem) === true) {
                        $ret .= $subItem->md(['headingLevel' => (intval($data['headingLevel']) + 2)]);
                    } else if ($subVal['type'] === 'objstr' && is_object($subItem) === true) {
                        $ret .= '- '.$subItem->md()."  \r\n";
                    }
                }

                if ($subVal['type'] === 'str' || $subVal['type'] === 'objstr') {
                    $ret .= "\r\n";
                }
            }//end foreach
        }//end if

        return $ret;

    }//end mdOutWithHeading()


    /**
     * Creates a standard Markdown documentation object.
     *
     * ```php
     * $data = [
     *   'objectType' => '',
     *   'objectName' => '',
     *   'props' => [
     *     '' => '', //string|?PDBasic
     *   ],
     * ```
     *
     * @param array $data Object data
     *
     * @return string
     */
    protected function mdOutNoHeading(array $data): string
    {
        $ret = sprintf(
            "%s <q>%s</q>",
            $data['objectType'],
            $data['objectName']
        );
        if (isset($data['props']) === true && is_array($data['props']) === true && count($data['props']) > 0) {
            foreach ($data['props'] as $propKey => $propVal) {
                if (is_string($propVal) === true && $propVal !== '') {
                    if (strcasecmp($propKey, 'line') === 0
                        || strcasecmp($propKey, 'path') === 0
                    ) {
                        $ret .= sprintf(
                            " %s %s",
                            $propKey,
                            $propVal
                        );
                    } else {
                        $ret .= sprintf(
                            " %s `%s`",
                            $propKey,
                            $propVal
                        );
                    }
                } else if (is_object($propVal) === true) {
                    $ret .= sprintf(
                        " %s `%s`",
                        $propKey,
                        $propVal->md()
                    );
                }//end if
            }//end foreach
        }//end if

        return $ret;

    }//end mdOutNoHeading()


}//end class
