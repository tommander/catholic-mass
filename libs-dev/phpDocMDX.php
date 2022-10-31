<?php
/**
 * Hello
 * 
 * 'file' => [
 *   'docblock' => [
 *   ],
 *   
 * ]
 */

namespace TMD\PDMDX;

class PDProject
{
    var $name; //string
    var $files; //PDFile[]
    var $namespaces; //PDNamespace[]

    public function __construct(PDElement $element)
    {
        $this->name = $element->getAttr('name', '');
        $this->namespaces = [];
        $this->files = [];

        if (is_array($element->children) === true) {
        foreach ($element->children as $projectChild) {
            if ($projectChild->name === 'namespace') {
                $this->namespaces[] = new PDNamespace($projectChild);
            } else if ($projectChild->name === 'file') {
                $this->files[] = new PDFile($projectChild);
            }
        }
        }
    }
}

class PDNamespace
{
    var $name; //string
    var $fullName; //string
    var $children; //PDNamespace[]

    public function __construct(PDElement $element) {
        $this->name = $element->getAttr('name', '');
        $this->fullName = $element->getAttr('full_name', '');
        $this->children = [];

        if (is_array($element->children) === true) {
        foreach ($element->children as $nsChild) {
            if ($nsChild->name === 'namespace') {
                $this->children[] = new PDNamespace($nsChild);
            }
        }
        }
    }
}

class PDFile
{
    var $path; //string
    var $hash; //string

    var $docBlock; //PDDocBlock
    var $includes; //string[]
    var $nsaliases; //string[]
    var $constants; //PDConstant[]
    var $functions; //PDFunction[]
    var $interfaces; //PDInterface[]
    var $classes; //PDClass[]
    var $traits; //PDTrait[]
    var $markers; //PDMarker
    var $errors; //PDError

    public function __construct(PDElement $element) {
        $this->path = $element->getAttr('path', '');
        $this->hash = $element->getAttr('hash', '');
        $this->docBlock = null;
        $this->includes = [];
        $this->nsaliases = [];
        $this->constants = [];
        $this->functions = [];
        $this->interfaces = [];
        $this->classes = [];
        $this->traits = [];
        $this->markers = [];
        $this->errors = [];

        if (is_array($element->children) === true) {
        foreach ($element->children as $fileChild) {
            if ($fileChild->name === 'include' && is_array($fileChild->children) === true) {
                foreach ($fileChild->children as $includeChild) {
                    if ($includeChild->name === 'name') {
                        $this->includes[] = $includeChild->content;
                        break;
                    }
                }
            } else if ($fileChild->name === 'docblock') {
                $this->docBlock = new PDDocBlock($fileChild);
            } else if ($fileChild->name === 'namespace-alias') {
                $this->nsaliases[] = $fileChild->getAttr('name', '');
            } else if ($fileChild->name === 'constant') {
                $this->constants[] = new PDConstant($fileChild);
            } else if ($fileChild->name === 'function') {
                $this->functions[] = new PDFunction($fileChild);
            } else if ($fileChild->name === 'interface') {
                $this->interfaces[] = new PDInterface($fileChild);
            } else if ($fileChild->name === 'class') {
                $this->classes[] = new PDClass($fileChild);
            } else if ($fileChild->name === 'trait') {
                $this->traits[] = new PDTrait($fileChild);
            } else if ($fileChild->name === 'parse_markers' && is_array($fileChild->children) === true) {
                foreach ($fileChild->children as $parseMarkersChild) {
                    if ($parseMarkersChild->name === 'error') {
                        $this->errors[] = new PDError($parseMarkersChild);
                    }
                }
            } else {
                $this->markers[] = new PDMarker($fileChild);
            }
        }
        }
    }
}

class PDConstant
{
    var $namespace; //string
    var $line; //string
    var $visibility; //string
    var $name; //string
    var $fullName; //string
    var $value; //string
    var $inheritedFrom; //string
    var $docBlock; //PDDocBlock
}

class PDDocBlock
{
    var $line; //string
    var $description; //string
    var $longDescription; //string
    var $tags; //PDTag[]
}

class PDTag
{
    var $name;
    var $description;
    var $link;
    var $version;
    var $variable;
    var $method;
    var $type;
}

class PDFunction
{
    var $namespace;
    var $line;
    var $package;
    var $returnByReference;
    var $name;
    var $fullName;
    var $docBlock;
    var $arguments;
}

class PDArgument
{
    var $line;
    var $byReference;
    var $name;
    var $default;
    var $type;
}

class PDInterface
{
    var $namespace;
    var $line;
    var $package;
    var $name;
    var $fullName;
    var $docBlock;
    var $extends;
    var $contants;
    var $methods;
}

class PDClass
{
    var $final;
    var $abstract;
    var $namespace;
    var $line;
    var $name;
    var $fullName;
    var $docBlock;
    var $extends;
    var $implements;
    var $constants;
    var $inheritedConstants;
    var $properties;
    var $inheritedProperties;
    var $methods;
    var $inheritedMethods;
}

class PDProperty
{
    var $namespace;
    var $line;
    var $visibility;
    var $name;
    var $fullName;
    var $default;
    var $inheritedFrom;
    var $docBlock;
}

class PDMethod
{
    var $final;
    var $abstract;
    var $static;
    var $namespace;
    var $line;
    var $visibility;
    var $returnByReference;
    var $name;
    var $fullName;
    var $value;
    var $inheritedFrom;
    var $arguments;
    var $docBlock;
}

class PDTrait
{
    var $namespace;
    var $line;
    var $name;
    var $fullName;
    var $docBlock;
    var $properties;
    var $methods;
}

class PDMarker
{
    var $type;
    var $line;
    var $message;
}

class PDError
{
    var $code;
    var $line;
}

/* * * * * * * */

class PDElement {
    var $name;
    var $attributes;
    var $content;
    var $children;

    public function getAttr($name, $default = '') {
        if (isset($this->attributes[$name]) === true) {
            return $this->attributes[$name];
        }
        return $default;
    }
};

class PDParser
{
    const PHPDOC_XML = '..\.docs\structure.xml';

    private function getStructure()
    {
        $xml = file_get_contents(__DIR__ . "\\" . self::PHPDOC_XML);
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $tags);
        xml_parser_free($parser);
      
        $elements = array();  // the currently filling [child] PDElement array
        $stack = array();
        foreach ($tags as $tag) {
          $index = count($elements);
          if ($tag['type'] == "complete" || $tag['type'] == "open") {
            $elements[$index] = new PDElement;
            $elements[$index]->name = $tag['tag'];
            $elements[$index]->attributes = isset($tag['attributes']) ? $tag['attributes'] : [];
            $elements[$index]->content = isset($tag['value']) ? $tag['value'] : '';
            if ($tag['type'] == "open") {  // push
              $elements[$index]->children = array();
              $stack[count($stack)] = &$elements;
              $elements = &$elements[$index]->children;
            }
          }
          if ($tag['type'] == "close") {  // pop
            $elements = &$stack[count($stack) - 1];
            unset($stack[count($stack) - 1]);
          }
        }
        return $elements[0];  // the single top-level element
    }

    public function run()
    {
        $root = $this->getStructure();

        $project = new PDProject($root);

        file_put_contents(__DIR__ . "\\out.txt", var_export($project, true));
    }
}

class PDGenerator
{

}

$pdmdxparser = new PDParser;
$pdmdxparser->run();
