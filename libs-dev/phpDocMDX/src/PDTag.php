<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDTag extends PDBasic
{
    const ELEM_NAME = 'tag';

    /**
     * Tag name
     *
     * @var string
     */
    public string $name;

    /**
     * Tag description
     *
     * @var string
     */
    public string $description;

    /**
     * Tag link
     *
     * @var string
     */
    public string $link;

    /**
     * Tag version
     *
     * @var string
     */
    public string $version;

    /**
     * Tag variable
     *
     * @var string
     */
    public string $variable;

    /**
     * Tag method
     *
     * @var string
     */
    public string $method;

    /**
     * Tag type
     *
     * @var string
     */
    public string $type;


    /**
     * Constructor
     *
     * @param PDElement $element Parsed XML element
     */
    public function __construct(PDElement $element)
    {
        $this->name        = $element->getAttr('name', '');
        $this->description = $element->getAttr('description', '');
        $this->link        = $element->getAttr('link', '');
        $this->version     = $element->getAttr('version', '');
        $this->variable    = $element->getAttr('variable', '');
        $this->method      = $element->getAttr('method_name', '');
        $this->type        = $element->getAttr('type', '');

    }//end __construct()


    /**
     * Output tag in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Tag',
            'objectName'   => $this->name,
            'props'        => [
                'Description' => $this->description,
                'Link'        => $this->link,
                'Version'     => $this->version,
                'Variable'    => $this->variable,
                'Method'      => $this->method,
                'Type'        => $this->type,
            ],
        ];

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
