<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\PDMDX;

class PDElement
{

    /**
     * Element name
     *
     * @var string
     */
    public $name = '';

    /**
     * Element attributes
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Element content
     *
     * @var string
     */
    public $content = '';

    /**
     * Element children
     *
     * @var array
     */
    public $children = [];


    /**
     * Get attribute value, if it exists, or default value
     *
     * @param string $name    Attribute name
     * @param string $default Default value (default `''`)
     *
     * @return string
     */
    public function getAttr($name, $default='')
    {
        if (isset($this->attributes[$name]) === true) {
            return $this->attributes[$name];
        }

        return $default;

    }//end getAttr()


}//end class
