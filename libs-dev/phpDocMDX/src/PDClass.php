<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDClass extends PDBasic
{
    const ELEM_NAME = 'class';

    /**
     * Class is final? `true` or `false`
     *
     * @var string
     */
    public $final;

    /**
     * Class is abstract? `true` or `false`
     *
     * @var string
     */
    public $abstract;

    /**
     * Class namespace
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
     * Class name
     *
     * @var string
     */
    public $name = '';

    /**
     * Class full name
     *
     * @var string
     */
    public $fullName = '';

    /**
     * Class DocBlock
     *
     * @var ?PDDocBlock
     */
    public $docBlock = null;

    /**
     * Class extends
     *
     * @var string[]
     */
    public $extends = [];

    /**
     * Class implements
     *
     * @var string[]
     */
    public $implements = [];

    /**
     * Class constants
     *
     * @var PDConstant[]
     */
    public $constants = [];

    /**
     * Class properties
     *
     * @var PDProperty[]
     */
    public $properties = [];

    /**
     * Class methods
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
        $this->final      = $element->getAttr('final', '');
        $this->abstract   = $element->getAttr('abstract', '');
        $this->namespace  = $element->getAttr('namespace', '');
        $this->line       = $element->getAttr('line', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $classChild) {
                if ($classChild->name === 'name') {
                    $this->name = $classChild->content;
                } else if ($classChild->name === 'full_name') {
                    $this->fullName = $classChild->content;
                } else if ($classChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($classChild, $this->sourceFile);
                } else if ($classChild->name === 'extends') {
                    $this->extends[] = $classChild->content;
                } else if ($classChild->name === 'implements') {
                    $this->implements[] = $classChild->content;
                } else if ($classChild->name === PDConstant::ELEM_NAME) {
                    $this->constants[] = new PDConstant($classChild, $this->sourceFile);
                } else if ($classChild->name === PDProperty::ELEM_NAME) {
                    $this->properties[] = new PDProperty($classChild, $this->sourceFile);
                } else if ($classChild->name === PDMethod::ELEM_NAME) {
                    $this->methods[] = new PDMethod($classChild, $this->sourceFile);
                }
            }
        }//end if

    }//end __construct()


    /**
     * Output class in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Class',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Full name' => $this->fullName,
                'Namespace' => $this->namespace,
                'Line'      => PDHelper::ghLink($this->sourceFile, $this->line),
            ],
            'sub'          => [
                'Extends'    => [
                    'type'  => 'str',
                    'items' => $this->extends,
                ],
                'Implements' => [
                    'type'  => 'str',
                    'items' => $this->implements,
                ],
                'Constants'  => [
                    'type'  => 'obj',
                    'items' => $this->constants,
                ],
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

        if ($this->final === 'true') {
            $data['objectType'] = 'Final class';
        } else if ($this->abstract === 'true') {
            $data['objectType'] = 'Abstract class';
        }

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
