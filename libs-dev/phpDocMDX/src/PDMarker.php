<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDMarker extends PDBasic
{

    /**
     * Marker type
     *
     * @var string
     */
    public $type;

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
     * Marker message (description)
     *
     * @var string
     */
    public $message;


    /**
     * Constructor
     *
     * @param PDElement $element    Parsed XML element
     * @param string    $sourceFile Source file
     */
    public function __construct(PDElement $element, string $sourceFile)
    {
        $this->sourceFile = $sourceFile;
        $this->type       = $element->name;
        $this->line       = $element->getAttr('line', '');
        $this->message    = $element->content;

    }//end __construct()


    /**
     * Output marker in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        $data = [
            'headingLevel' => 0,
            'objectType'   => 'Marker',
            'objectName'   => $this->type,
            'props'        => [
                'Line'    => PDHelper::ghLink($this->sourceFile, $this->line),
                'Message' => $this->message,
            ],
        ];

        if (isset($opts['headingLevel']) === true) {
            $data['headingLevel'] = intval($opts['headingLevel']);
            return $this->mdOutWithHeading($data);
        }

        return $this->mdOutNoHeading($data);

    }//end md()


}//end class
