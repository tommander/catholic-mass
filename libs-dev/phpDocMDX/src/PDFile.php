<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDFile extends PDBasic
{
    const ELEM_NAME = 'file';

    /**
     * File path relative to the root workspace folder
     *
     * @var string
     */
    public string $path;

    /**
     * Hash of the file
     *
     * @var string
     */
    public string $hash;

    /**
     * File's DocBlock
     *
     * @var ?PDDocBlock
     */
    public ?PDDocBlock $docBlock = null;

    /**
     * Includes
     *
     * @var string[]
     */
    public array $includes = [];

    /**
     * Namespace aliases
     *
     * @var string[]
     */
    public array $nsaliases = [];

    /**
     * Constants
     *
     * @var PDConstant[]
     */
    public array $constants = [];

    /**
     * Functions
     *
     * @var PDFunction[]
     */
    public array $functions = [];

    /**
     * Interfaces
     *
     * @var PDInterface[]
     */
    public array $interfaces = [];

    /**
     * Classes
     *
     * @var PDClass[]
     */
    public array $classes = [];

    /**
     * Traits
     *
     * @var PDTrait[]
     */
    public array $traits = [];

    /**
     * Markers
     *
     * @var PDMarker[]
     */
    public array $markers = [];

    /**
     * Errors
     *
     * @var PDError[]
     */
    public array $errors = [];

    /**
     * Output directory
     *
     * @var string
     */
    private string $dir = '';


    /**
     * Constructor
     *
     * @param PDElement $element Parsed XML element
     * @param string    $dir     Output directory
     */
    public function __construct(PDElement $element, string $dir)
    {
        $this->dir  = $dir;
        $this->path = $element->getAttr('path', '');
        $this->hash = $element->getAttr('hash', '');

        foreach ($element->children as $fileChild) {
            if ($fileChild->name === 'include' && is_array($fileChild->children) === true) {
                foreach ($fileChild->children as $includeChild) {
                    if ($includeChild->name === 'name') {
                        $this->includes[] = $includeChild->content;
                        break;
                    }
                }
            } else if ($fileChild->name === PDDocBlock::ELEM_NAME) {
                $this->docBlock = new PDDocBlock($fileChild, $this->path);
            } else if ($fileChild->name === 'namespace-alias') {
                $this->nsaliases[] = $fileChild->getAttr('name', '');
            } else if ($fileChild->name === PDConstant::ELEM_NAME) {
                $this->constants[] = new PDConstant($fileChild, $this->path);
            } else if ($fileChild->name === PDFunction::ELEM_NAME) {
                $this->functions[] = new PDFunction($fileChild, $this->path);
            } else if ($fileChild->name === PDInterface::ELEM_NAME) {
                $this->interfaces[] = new PDInterface($fileChild, $this->path);
            } else if ($fileChild->name === PDClass::ELEM_NAME) {
                $this->classes[] = new PDClass($fileChild, $this->path);
            } else if ($fileChild->name === PDTrait::ELEM_NAME) {
                $this->traits[] = new PDTrait($fileChild, $this->path);
            } else if ($fileChild->name === 'parse_markers' && is_array($fileChild->children) === true) {
                foreach ($fileChild->children as $parseMarkersChild) {
                    if ($parseMarkersChild->name === PDError::ELEM_NAME) {
                        $this->errors[] = new PDError($parseMarkersChild, $this->path);
                    }
                }
            } else if ($fileChild->name !== 'parse_markers') {
                $this->markers[] = new PDMarker($fileChild, $this->path);
            }//end if
        }//end foreach

    }//end __construct()


    /**
     * Sanitize a string to be used as a filename.
     *
     * Allowed characters are:
     *
     * - Latin letters
     * - Decimal digits
     * - Underscore and dash
     *
     * Everything else is replaced by underscore.
     *
     * @param string $dirty Input text.
     *
     * @return string
     */
    private function makeFilename(string $dirty): string
    {
        return preg_replace('/[^A-z0-9_-]/', '_', $dirty);

    }//end makeFilename()


    /**
     * Prepares a Markdown file for the file's content documentation and returns a link to that file.
     *
     * Options keys:
     *
     * - `dir` - Documentation output directory
     * - `toc` - Reference to raw ToC array
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 1,
            'objectType'   => 'File',
            'objectName'   => basename($this->path),
            'docBlock'     => $this->docBlock,
            'props'        => [
                'Path' => PDHelper::ghLink($this->path, ''),
                'Hash' => $this->hash,
            ],
            'sub'          => [
                'Includes'   => [
                    'type'  => 'str',
                    'items' => $this->includes,
                ],
                'NS Aliases' => [
                    'type'  => 'str',
                    'items' => $this->nsaliases,
                ],
                'Constants'  => [
                    'type'  => 'obj',
                    'items' => $this->constants,
                ],
                'Functions'  => [
                    'type'  => 'obj',
                    'items' => $this->functions,
                ],
                'Interfaces' => [
                    'type'  => 'obj',
                    'items' => $this->interfaces,
                ],
                'Classes'    => [
                    'type'  => 'obj',
                    'items' => $this->classes,
                ],
                'Traits'     => [
                    'type'  => 'obj',
                    'items' => $this->traits,
                ],
                'Markers'    => [
                    'type'  => 'objstr',
                    'items' => $this->markers,
                ],
                'Errors'     => [
                    'type'  => 'objstr',
                    'items' => $this->errors,
                ],
            ],
        ];

        $fileName = 'phpdoc_'.$this->makeFilename($this->path);
        file_put_contents($this->dir.$fileName.'.md', $this->mdOutWithHeading($data));
        return sprintf("[%s](%s)", basename($this->path), $fileName);

    }//end md()


}//end class
