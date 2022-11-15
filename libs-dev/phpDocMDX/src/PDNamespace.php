<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDNamespace extends PDBasic
{
    const ELEM_NAME = 'namespace';

    /**
     * Namespace name
     *
     * @var string
     */
    public $name;

    /**
     * Full namespace name
     *
     * @var string
     */
    public $fullName;

    /**
     * Parent namespaces
     *
     * @var PDNamespace[]
     */
    public $parents = [];


    /**
     * Constructor
     *
     * @param PDElement $element Parsed XML element
     */
    public function __construct(PDElement $element)
    {
        $this->name     = $element->getAttr('name', '');
        $this->fullName = $element->getAttr('full_name', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $nsParent) {
                if ($nsParent->name === PDNamespace::ELEM_NAME) {
                    $this->parents[] = new PDNamespace($nsParent);
                }
            }
        }

    }//end __construct()


    /**
     * Returns namespace name and full name in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Namespace',
            'objectName'   => $this->name,
            'props'        => [
                'Full name' => $this->fullName,
            ],
            'sub'          => [
                'Parents' => [
                    'type'  => 'obj',
                    'items' => $this->parents,
                ],
            ],
        ];

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
