<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDProject extends PDBasic
{
    const ELEM_NAME = 'project';

    /**
     * Project name
     *
     * @var string
     */
    public string $name;

    /**
     * List of project files
     *
     * @var PDFile[]
     */
    public array $files = [];

    /**
     * List of project namespaces
     *
     * @var PDNamespace[]
     */
    public array $namespaces = [];

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
        $this->name = $element->getAttr('name', '');

        foreach ($element->children as $projectChild) {
            if ($projectChild->name === PDNamespace::ELEM_NAME) {
                $this->namespaces[] = new PDNamespace($projectChild);
            } else if ($projectChild->name === PDFile::ELEM_NAME) {
                $this->files[] = new PDFile($projectChild, $dir);
            }
        }

    }//end __construct()


    /**
     * Returns a *Table of Contents* in the form of a multi-level unordered list
     *
     * @param array $toc        Raw ToC array
     * @param int   $startLevel Depth level (starts at 0)
     *
     * @return string
     */
    private function outputToc(array $toc, int $startLevel): string
    {
        $text = '';
        foreach ($toc as $tocKey => $tocVal) {
            $text .= sprintf("%s- %s\r\n", str_repeat(' ', ($startLevel * 2)), $tocKey);
            if (is_array($tocVal) === true && count($tocVal) > 0) {
                $text .= $this->outputToc($tocVal, ($startLevel + 1));
            }
        }

        return $text;

    }//end outputToc()


    /**
     * Prepare the project's index file (`index.md`).
     *
     * @param array $opts Expects key `dir`, which is the output directory for this project's documentation.
     *
     * @return string Just for fun; always returns an empty string.
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 1,
            'objectType'   => 'Project',
            'objectName'   => $this->name,
            'sub'          => [
                'Files'      => [
                    'type'  => 'objstr',
                    'items' => $this->files,
                ],
                'Namespaces' => [
                    'type'  => 'objstr',
                    'items' => $this->namespaces,
                ],
            ],
        ];

        file_put_contents($this->dir.'phpdoc_index.md', $this->mdOutWithHeading($data));
        return '';

    }//end md()


}//end class
