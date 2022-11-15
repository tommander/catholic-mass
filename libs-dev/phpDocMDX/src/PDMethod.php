<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDMethod extends PDBasic
{
    const ELEM_NAME = 'method';

    /**
     * Is method final? `true` or `false`
     *
     * @var string
     */
    public $final;

    /**
     * Is method abstract? `true` or `false`
     *
     * @var string
     */
    public $abstract;

    /**
     * If method static? `true` or `false`
     *
     * @var string
     */
    public $static;

    /**
     * Method namespace
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
     * Method visibility
     *
     * @var string
     */
    public $visibility;

    /**
     * Return by reference?
     *
     * @var string
     */
    public $returnByReference;

    /**
     * Method name
     *
     * @var string
     */
    public $name = '';

    /**
     * Method full name
     *
     * @var string
     */
    public $fullName = '';

    /**
     * Method value
     *
     * @var string
     */
    public $value = '';

    /**
     * Method inherited from
     *
     * @var string
     */
    public $inheritedFrom = '';

    /**
     * Method arguments
     *
     * @var PDArgument[]
     */
    public $arguments = [];

    /**
     * Method DocBlock
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
        $this->final      = $element->getAttr('final', '');
        $this->abstract   = $element->getAttr('abstract', '');
        $this->static     = $element->getAttr('static', '');
        $this->namespace  = $element->getAttr('namespace', '');
        $this->line       = $element->getAttr('line', '');
        $this->returnByReference = $element->getAttr('returnByReference', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $methodChild) {
                if ($methodChild->name === 'name') {
                    $this->name = $methodChild->content;
                } else if ($methodChild->name === 'full_name') {
                    $this->fullName = $methodChild->content;
                } else if ($methodChild->name === 'value') {
                    $this->value = $methodChild->content;
                } else if ($methodChild->name === 'inherited_from') {
                    $this->inheritedFrom = $methodChild->content;
                } else if ($methodChild->name === PDArgument::ELEM_NAME) {
                    $this->docBlock = new PDArgument($methodChild, $this->sourceFile);
                } else if ($methodChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($methodChild, $this->sourceFile);
                }
            }
        }

    }//end __construct()


    /**
     * Output method in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Method',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Namespace'           => $this->namespace,
                'Line'                => PDHelper::ghLink($this->sourceFile, $this->line),
                'Visibility'          => $this->visibility,
                'Return by reference' => $this->returnByReference,
                'Full name'           => $this->fullName,
                'Value'               => $this->value,
                'Inherited from'      => $this->inheritedFrom,
            ],
            'sub'          => [
                'Arguments' => [
                    'type'  => 'obj',
                    'items' => $this->arguments,
                ],
            ],
        ];

        if ($this->final === 'true') {
            if ($this->static === 'true') {
                $data['objectType'] = 'Final static method';
            } else {
                $data['objectType'] = 'Final method';
            }
        } else if ($this->abstract === 'true') {
            if ($this->static === 'true') {
                $data['objectType'] = 'Abstract static method';
            } else {
                $data['objectType'] = 'Abstract method';
            }
        } else if ($this->static === 'true') {
            $data['objectType'] = 'Static method';
        }

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
