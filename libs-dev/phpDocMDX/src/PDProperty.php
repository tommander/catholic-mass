<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\PDMDX;

class PDProperty extends PDBasic
{
    const ELEM_NAME = 'property';

    /**
     * Property namespace
     *
     * @var string
     */
    public $namespace;

    /**
     * Source file
     *
     * @var string
     */
    public $sourceFile;

    /**
     * Line in code
     *
     * @var string
     */
    public $line;

    /**
     * Property visibility
     *
     * @var string
     */
    public $visibility;

    /**
     * Property name
     *
     * @var string
     */
    public $name = '';

    /**
     * Property full name
     *
     * @var string
     */
    public $fullName = '';

    /**
     * Property default value
     *
     * @var string
     */
    public $default = '';

    /**
     * Property is inherited from
     *
     * @var string
     */
    public $inheritedFrom = '';

    /**
     * Property DocBlock
     *
     * @var ?PDDocBlock
     */
    public $docBlock = null;


    /**
     * Constructor
     *
     * @param PDElement $element    Parsed XML element
     * @param string    $sourceFile Source file
     */
    public function __construct(PDElement $element, string $sourceFile)
    {
        $this->sourceFile = $sourceFile;
        $this->namespace  = $element->getAttr('namespace', '');
        $this->line       = $element->getAttr('line', '');
        $this->visibility = $element->getAttr('visibility', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $propChild) {
                if ($propChild->name === 'name') {
                    $this->name = $propChild->content;
                } else if ($propChild->name === 'full_name') {
                    $this->fullName = $propChild->content;
                } else if ($propChild->name === 'default') {
                    $this->default = $propChild->content;
                } else if ($propChild->name === 'inherited_from') {
                    $this->inheritedFrom = $propChild->content;
                } else if ($propChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($propChild, $this->sourceFile);
                }
            }
        }

    }//end __construct()


    /**
     * Output property to Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Property',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Namespace'      => $this->namespace,
                'Line'           => PDHelper::ghLink($this->sourceFile, $this->line),
                'Visibility'     => $this->visibility,
                'Full name'      => $this->fullName,
                'Default'        => $this->default,
                'Inherited from' => $this->inheritedFrom,
            ],
        ];

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
