<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDError extends PDBasic
{
    const ELEM_NAME = 'error';

    /**
     * Error code
     *
     * @var string
     */
    public string $code;

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
     * Constructor
     *
     * @param PDElement $element    Parsed XML element
     * @param string    $sourceFile Source file
     */
    public function __construct(PDElement $element, string $sourceFile)
    {
        $this->sourceFile = $sourceFile;
        $this->code       = $element->content;
        $this->line       = $element->getAttr('line', '');

    }//end __construct()


    /**
     * Output error in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Error',
            'objectName'   => $this->code,
            'props'        => [
                'Line' => PDHelper::ghLink($this->sourceFile, $this->line),
            ],
        ];

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
