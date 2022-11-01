<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license GPL 3.0 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TMD\PDMDX;

class PDArgument extends PDBasic
{
    const ELEM_NAME = 'argument';

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
     * By reference?
     *
     * @var string
     */
    public $byReference;

    /**
     * Argument name
     *
     * @var string
     */
    public $name = '';

    /**
     * Argument default value
     *
     * @var string
     */
    public $default = '';

    /**
     * Argument type
     *
     * @var string
     */
    public $type = '';


    /**
     * Constructor
     *
     * @param PDElement $element    Parsed XML element
     * @param string    $sourceFile Source file
     */
    public function __construct(PDElement $element, string $sourceFile)
    {
        $this->sourceFile  = $sourceFile;
        $this->line        = $element->getAttr('line', '');
        $this->byReference = $element->getAttr('by_reference', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $funcChild) {
                if ($funcChild->name === 'name') {
                    $this->name = $funcChild->content;
                } else if ($funcChild->name === 'default') {
                    $this->default = $funcChild->content;
                } else if ($funcChild->name === 'type') {
                    $this->type = $funcChild->content;
                }
            }
        }

    }//end __construct()


    /**
     * Output argument in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Argument',
            'objectName'   => $this->name,
            'props'        => [
                'Type'         => $this->type,
                'Default'      => $this->default,
                'By reference' => $this->byReference,
                'Line'         => PDHelper::ghLink($this->sourceFile, $this->line),
            ],
        ];

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
