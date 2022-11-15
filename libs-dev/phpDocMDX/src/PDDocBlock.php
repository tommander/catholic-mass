<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDDocBlock extends PDBasic
{
    const ELEM_NAME = 'docblock';

    /**
     * Source file
     *
     * @var string
     */
    public $sourceFile;

    /**
     * Line in the code
     *
     * @var string
     */
    public $line;

    /**
     * Short description
     *
     * @var string
     */
    public $description = '';

    /**
     * Long description
     *
     * @var string
     */
    public $longDescription = '';

    /**
     * DocBlock tags
     *
     * @var PDTag[]
     */
    public $tags = [];


    /**
     * Constructor
     *
     * @param PDElement $element    Parsed XML element
     * @param string    $sourceFile Source file
     */
    public function __construct(PDElement $element, string $sourceFile)
    {
        $this->sourceFile = $sourceFile;
        $this->line       = $element->getAttr('line', '');

        if (is_array($element->children) === true) {
            foreach ($element->children as $constChild) {
                if ($constChild->name === 'description' && is_string($constChild->content) === true) {
                    $this->description = $constChild->content;
                } else if ($constChild->name === 'long-description' && is_string($constChild->content) === true) {
                    $this->longDescription = $constChild->content;
                } else if ($constChild->name === 'tags' && is_array($constChild->children) === true) {
                    foreach ($constChild->children as $tagsChild) {
                        $this->tags[] = new PDTag($tagsChild);
                    }
                }
            }
        }

    }//end __construct()


    /**
     * Returns DocBlock in Markdown
     *
     * @param array $opts Options (default `[]`)
     *
     * @return string
     */
    public function md(array $opts=[]): string
    {
        if (isset($opts['singleLine']) === true && $opts['singleLine'] === true) {
            $text = $this->description;
            if ($this->longDescription !== '') {
                $text .= ' '.$this->longDescription;
            }

            foreach ($this->tags as $tag) {
                $text .= ' '.$this->tag->md();
            }

            return $text;
        }

        $text = sprintf("%s\r\n\r\n", $this->description);
        if ($this->longDescription !== '') {
            $text .= sprintf("%s\r\n\r\n", $this->longDescription);
        }

        if (count($this->tags) > 0) {
            foreach ($this->tags as $tag) {
                $text .= '- '.$this->tag->md()."\r\n";
            }

            $text .= "\r\n";
        }

        return $text;

    }//end md()


}//end class
