<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\PDMDX;

class PDHelper
{


    /**
     * Create dir/file path.
     *
     * @param string|array $segments   Segments of the path
     * @param bool         $prependDir Whether to prepend `__DIR__` (default `true`)
     *
     * @return string
     */
    public static function makePath($segments, bool $prependDir=true): string
    {
        $ret = '';
        if ($prependDir === true) {
            $ret = __DIR__.DIRECTORY_SEPARATOR;
        }

        if (is_array($segments) === true) {
            return $ret.implode(DIRECTORY_SEPARATOR, $segments);
        }

        return $ret.str_replace('/', DIRECTORY_SEPARATOR, $segments);

    }//end makePath()


    /**
     * Create GitHub source code link.
     *
     * @param string $file Relative file path
     * @param string $line Line number
     *
     * @return string
     */
    public static function ghLink(string $file, string $line=''): string
    {
        if ($line === '') {
            return sprintf(
                '[%1$s](https://github.com/tommander/catholic-mass/blob/main/%1$s)',
                $file
            );
        }

        return sprintf(
            '[%1$s (line %2$s)](https://github.com/tommander/catholic-mass/blob/main/%1$s#L%2$s)',
            $file,
            $line
        );

    }//end ghLink()


}//end class
