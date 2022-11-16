<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDFunction extends PDBasic
{
    const ELEM_NAME = 'function';

    /**
     * Function namespace
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
     * Function package
     *
     * @var string
     */
    public string $package;

    /**
     * Return by reference?
     *
     * @var string
     */
    public string $returnByReference;

    /**
     * Function name
     *
     * @var string
     */
    public string $name = '';

    /**
     * Function full name
     *
     * @var string
     */
    public string $fullName = '';

    /**
     * Function DocBlock
     *
     * @var ?PDDocBlock
     */
    public ?PDDocBlock $docBlock = null;

    /**
     * Function arguments
     *
     * @var PDArgument[]
     */
    public array $arguments = [];


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
        $this->returnByReference = $element->getAttr('returnByReference', '');

        foreach ($element->children as $funcChild) {
            if ($funcChild->name === 'name') {
                $this->name = $funcChild->content;
            } else if ($funcChild->name === 'full_name') {
                $this->fullName = $funcChild->content;
            } else if ($funcChild->name === PDDocBlock::ELEM_NAME) {
                $this->docBlock = new PDDocBlock($funcChild, $this->sourceFile);
            } else if ($funcChild->name === PDArgument::ELEM_NAME) {
                $this->arguments[] = new PDArgument($funcChild, $this->sourceFile);
            }
        }

    }//end __construct()


    /**
     * Output function in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Function',
            'objectName'   => $this->name,
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Namespace'           => $this->namespace,
                'Line'                => PDHelper::ghLink($this->sourceFile, $this->line),
                'Package'             => $this->package,
                'Return by reference' => $this->returnByReference,
                'Full name'           => $this->fullName,
            ],
            'sub'          => [
                'Arguments' => [
                    'type'  => 'obj',
                    'items' => $this->arguments,
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
