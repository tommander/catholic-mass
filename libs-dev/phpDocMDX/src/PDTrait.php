<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDTrait extends PDBasic
{
    const ELEM_NAME = 'trait';

    /**
     * Trait namespace
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
     * Trait name
     *
     * @var string
     */
    public $name = '';

    /**
     * Trait full name
     *
     * @var string
     */
    public $fullName = '';

    /**
     * Trait DocBlock
     *
     * @var ?PDDocBlock
     */
    public $docBlock = null;

    /**
     * Trait properties
     *
     * @var PDProperty[]
     */
    public $properties = [];

    /**
     * Trait methods
     *
     * @var PDMethod[]
     */
    public $methods = [];


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

        if (is_array($element->children) === true) {
            foreach ($element->children as $traitChild) {
                if ($traitChild->name === 'name') {
                    $this->name = $traitChild->content;
                } else if ($traitChild->name === 'full_name') {
                    $this->fullName = $traitChild->content;
                } else if ($traitChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($traitChild, $this->sourceFile);
                } else if ($traitChild->name === PDProperty::ELEM_NAME) {
                    $this->properties[] = new PDProperty($traitChild, $this->sourceFile);
                } else if ($traitChild->name === PDMethod::ELEM_NAME) {
                    $this->properties[] = new PDMethod($traitChild, $this->sourceFile);
                }
            }
        }

    }//end __construct()


    /**
     * Output trait in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Trait',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Namespace' => $this->namespace,
                'Line'      => PDHelper::ghLink($this->sourceFile, $this->line),
                'Full name' => $this->fullName,
            ],
            'sub'          => [
                'Properties' => [
                    'type'  => 'obj',
                    'items' => $this->properties,
                ],
                'Methods'    => [
                    'type'  => 'obj',
                    'items' => $this->methods,
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
