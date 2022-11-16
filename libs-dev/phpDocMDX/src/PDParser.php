<?php
/**
 * XXX
 *
 * @author  Tommander <tommander@tommander.cz>
 * @license MIT license https://opensource.org/licenses/MIT
 */

namespace TMD\PDMDX;

class PDParser
{

    /**
     * Input PHPDoc XML file
     *
     * @var string
     */
    private string $inputFile = '';

    /**
     * Output directory for Markdown files
     *
     * @var string
     */
    private string $outputDir = '';


    /**
     * Load PHPDoc XML output file and parse it into a general multi-level associative array
     *
     * @return PDElement
     */
    private function getStructure()
    {
        $xml = file_get_contents(PDHelper::makePath($this->inputFile));

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $tags);
        xml_parser_free($parser);

        $elements = [];
        $stack    = [];

        foreach ($tags as $tag) {
            $index = count($elements);
            if ($tag['type'] === "complete" || $tag['type'] === "open") {
                $elements[$index] = new PDElement;

                $elements[$index]->name       = $tag['tag'];
                $elements[$index]->attributes = [];
                if (isset($tag['attributes']) === true) {
                    $elements[$index]->attributes = $tag['attributes'];
                }

                $elements[$index]->content = '';
                if (isset($tag['value']) === true) {
                    $elements[$index]->content = $tag['value'];
                }

                if ($tag['type'] === "open") {
                    $elements[$index]->children = [];
                    $stack[count($stack)]       = &$elements;
                    $elements = &$elements[$index]->children;
                }
            }

            if ($tag['type'] === "close") {
                $elements = &$stack[(count($stack) - 1)];
                unset($stack[(count($stack) - 1)]);
            }
        }//end foreach

        return $elements[0];

    }//end getStructure()


    /**
     * Constructor
     *
     * @param string $xmlFile Input PHPDoc XML file (relative to this file's directory)
     * @param string $outDir  Output directory (relative to this file's directory)
     */
    public function __construct(string $xmlFile, string $outDir)
    {
        $this->inputFile = $xmlFile;
        $this->outputDir = $outDir;

    }//end __construct()


    /**
     * Parse XML and create Markdown out of it
     *
     * @return void
     */
    public function run()
    {
        $root = $this->getStructure();

        $project = new PDProject($root, PDHelper::makePath($this->outputDir));
        $project->md();

    }//end run()


}//end class
