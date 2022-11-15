<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDInterface extends PDBasic
{
    const ELEM_NAME = 'interface';

    /**
     * Interface namespace
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
     * Interface package
     *
     * @var string
     */
    public $package;

    /**
     * Interface name
     *
     * @var string
     */
    public $name = '';

    /**
     * Interface full name
     *
     * @var string
     */
    public $fullName = '';

    /**
     * Interface DocBlock
     *
     * @var ?PDDocBlock
     */
    public $docBlock = null;

    /**
     * Extends interfaces
     *
     * @var string[]
     */
    public $extends = [];

    /**
     * Interface constants
     *
     * @var PDConstant[]
     */
    public $constants = [];

    /**
     * Interface methods
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
        $this->package    = $element->getAttr('package', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $funcChild) {
                if ($funcChild->name === 'name') {
                    $this->name = $funcChild->content;
                } else if ($funcChild->name === 'full_name') {
                    $this->fullName = $funcChild->content;
                } else if ($funcChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($funcChild, $this->sourceFile);
                } else if ($funcChild->name === 'extends') {
                    $this->extends[] = $funcChild->content;
                } else if ($funcChild->name === PDConstant::ELEM_NAME) {
                    $this->constants[] = new PDConstant($funcChild, $this->sourceFile);
                } else if ($funcChild->name === PDMethod::ELEM_NAME) {
                    $this->methods[] = new PDMethod($funcChild, $this->sourceFile);
                }
            }
        }

    }//end __construct()


    /**
     * Output interface in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Interface',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Namespace' => $this->namespace,
                'Line'      => PDHelper::ghLink($this->sourceFile, $this->line),
                'Package'   => $this->package,
                'Full name' => $this->fullName,
            ],
            'sub'          => [
                'Extends'   => [
                    'type'  => 'str',
                    'items' => $this->extends,
                ],
                'Constants' => [
                    'type'  => 'obj',
                    'items' => $this->constants,
                ],
                'Methods'   => [
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
