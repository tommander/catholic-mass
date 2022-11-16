<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDConstant extends PDBasic
{
    const ELEM_NAME = 'constant';

    /**
     * Namespace
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
     * Line in the code
     *
     * @var string
     */
    public string $line;

    /**
     * Constant's visibility
     *
     * @var string
     */
    public string $visibility;

    /**
     * Constant's name
     *
     * @var string
     */
    public string $name = '';

    /**
     * Constant's full name
     *
     * @var string
     */
    public string $fullName = '';

    /**
     * Constant's value
     *
     * @var string
     */
    public string $value = '';

    /**
     * Inherited from
     *
     * @var string
     */
    public string $inheritedFrom = '';

    /**
     * Constant's DocBlock
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

        foreach ($element->children as $constChild) {
            if ($constChild->name === 'name') {
                $this->name = $constChild->content;
            } else if ($constChild->name === 'full_name') {
                $this->fullName = $constChild->content;
            } else if ($constChild->name === 'value') {
                $this->value = $constChild->content;
            } else if ($constChild->name === 'inherited_from') {
                $this->inheritedFrom = $constChild->content;
            } else if ($constChild->name === PDDocBlock::ELEM_NAME) {
                $this->docBlock = new PDDocBlock($constChild, $this->sourceFile);
            }
        }

    }//end __construct()


    /**
     * Output constant in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Constant',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Namespace'      => $this->namespace,
                'Line'           => PDHelper::ghLink($this->sourceFile, $this->line),
                'Visibility'     => $this->visibility,
                'Full name'      => $this->fullName,
                'Value'          => $this->value,
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
