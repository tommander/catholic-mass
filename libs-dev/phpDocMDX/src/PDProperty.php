<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
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
    public string $namespace;

    /**
     * Source file
     *
     * @var string
     */
    public string $sourceFile;

    /**
     * Line in code
     *
     * @var string
     */
    public string $line;

    /**
     * Property visibility
     *
     * @var string
     */
    public string $visibility;

    /**
     * Property name
     *
     * @var string
     */
    public string $name = '';

    /**
     * Property full name
     *
     * @var string
     */
    public string $fullName = '';

    /**
     * Property default value
     *
     * @var string
     */
    public string $default = '';

    /**
     * Property is inherited from
     *
     * @var string
     */
    public string $inheritedFrom = '';

    /**
     * Property DocBlock
     *
     * @var ?PDDocBlock
     */
    public ?PDDocBlock $docBlock = null;


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
