<?php
/**
 * Hello
 */

namespace TMD\PDMDX;

class PDProject
{
    const ELEM_NAME = 'project';

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
            if ($projectChild->name === PDNamespace::ELEM_NAME) {
                $this->namespaces[] = new PDNamespace($projectChild);
            } else if ($projectChild->name === PDFile::ELEM_NAME) {
                $this->files[] = new PDFile($projectChild);
            }
        }
        }
    }

    private function outputToc(array $toc, int $startLevel): string
    {
        $text = '';
        foreach ($toc as $tocKey=>$tocVal) {
            $text .= sprintf("%s- %s\r\n", str_repeat(' ', ($startLevel*2)), $tocKey);
            if (is_array($tocVal) === true && count($tocVal) > 0) {
                $text .= $this->outputToc($tocVal, ($startLevel+1));
            }
        }
        return $text;
    }

    public function md(string $dir): void
    {
        $text = sprintf("# Project <q>%s</q>\r\n\r\n", $this->name);
        if (count($this->namespaces) > 0) {
            $text .= "## Files\r\n\r\n";
            foreach ($this->files as $file) {
                $toc = [];
                $text .= "- ".$file->md($dir, $toc)."\r\n";
                $text .= $this->outputToc($toc, 1);
            }
            $text .= "\r\n";
        }

        if (count($this->namespaces) > 0) {
            $text .= "## Namespaces\r\n\r\n";
            foreach ($this->namespaces as $namespace) {
                $text .= "- ".$namespace->md()."\r\n";
            }
            $text .= "\r\n";
        }

        file_put_contents($dir . 'index.md', $text);
    }
}

class PDNamespace
{
    const ELEM_NAME = 'namespace';

    var $name; //string
    var $fullName; //string
    var $children; //PDNamespace[]

    public function __construct(PDElement $element) {
        $this->name = $element->getAttr('name', '');
        $this->fullName = $element->getAttr('full_name', '');
        $this->children = [];

        if (is_array($element->children) === true) {
            foreach ($element->children as $nsChild) {
                if ($nsChild->name === PDNamespace::ELEM_NAME) {
                    $this->children[] = new PDNamespace($nsChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = sprintf('<q>%s</q> (%s)', $this->name, $this->fullName);
/*        foreach ($this->children as $child) {
            $childText = $child->md();
            $text .= '  '.\str_replace("\r\n", "\r\n  ", $childText);
        }*/
        return $text;
    }
}

class PDFile
{
    const ELEM_NAME = 'file';

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
                } else if ($fileChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($fileChild);
                } else if ($fileChild->name === 'namespace-alias') {
                    $this->nsaliases[] = $fileChild->getAttr('name', '');
                } else if ($fileChild->name === PDConstant::ELEM_NAME) {
                    $this->constants[] = new PDConstant($fileChild);
                } else if ($fileChild->name === PDFunction::ELEM_NAME) {
                    $this->functions[] = new PDFunction($fileChild);
                } else if ($fileChild->name === PDInterface::ELEM_NAME) {
                    $this->interfaces[] = new PDInterface($fileChild);
                } else if ($fileChild->name === PDClass::ELEM_NAME) {
                    $this->classes[] = new PDClass($fileChild);
                } else if ($fileChild->name === PDTrait::ELEM_NAME) {
                    $this->traits[] = new PDTrait($fileChild);
                } else if ($fileChild->name === 'parse_markers' && is_array($fileChild->children) === true) {
                    foreach ($fileChild->children as $parseMarkersChild) {
                        if ($parseMarkersChild->name === PDError::ELEM_NAME) {
                            $this->errors[] = new PDError($parseMarkersChild);
                        }
                    }
                } else if ($fileChild->name !== 'parse_markers') {
                    $this->markers[] = new PDMarker($fileChild);
                }
            }
        }
    }

    private function makeFilename(string $dirty): string
    {
        return preg_replace('/[^A-z0-9_-]/', '_', $dirty);
    }

    public function md(string $dir, array &$toc): string
    {
        $text = sprintf("# %s\r\n", basename($this->path));
        if ($this->docBlock !== null) {
            $text .= $this->docBlock->md();
        }

        if (count($this->includes) > 0) {
            $text .= "## Includes\r\n\r\n";
            foreach ($this->includes as $include) {
                $text .= '- '.$include."\r\n";
            }
            $text .= "\r\n";
        }
        if (count($this->nsaliases) > 0) {
            $text .= "## NS Aliases\r\n\r\n";
            foreach ($this->nsaliases as $nsalias) {
                $text .= '- '.$nsalias."\r\n";
            }
            $text .= "\r\n";
        }
        if (count($this->constants) > 0) {
            $text .= "## Constants\r\n\r\n";
            foreach ($this->constants as $constant) {
                $toc[sprintf('Constant <q>%s</q>', $constant->name)] = null;
                $text .= $constant->md();
            }
        }
        if (count($this->functions) > 0) {
            $text .= "## Functions\r\n\r\n";
            foreach ($this->functions as $function) {
                $toc[sprintf('Function <q>%s</q>', $function->name)] = null;
                $text .= $function->md();
            }
        }
        if (count($this->interfaces) > 0) {
            $text .= "## Interfaces\r\n\r\n";
            foreach ($this->interfaces as $interface) {
                $toc[sprintf('Interface <q>%s</q>', $interface->name)] = null;
                $text .= $interface->md();
            }
        }
        if (count($this->classes) > 0) {
            $text .= "## Classes\r\n\r\n";
            foreach ($this->classes as $class) {
                $toc[sprintf('Class <q>%s</q>', $class->name)] = null;
                $text .= $class->md();
            }
        }
        if (count($this->traits) > 0) {
            $text .= "## Traits\r\n\r\n";
            foreach ($this->traits as $trait) {
                $toc[sprintf('Trait <q>%s</q>', $trait->name)] = null;
                $text .= $trait->md();
            }
        }
        if (count($this->markers) > 0) {
            $text .= "## Markers\r\n\r\n";
            foreach ($this->markers as $marker) {
                $text .= '- '.$marker->md()."\r\n";
            }
            $text .= "\r\n";
        }
        if (count($this->errors) > 0) {
            $text .= "## Errors\r\n\r\n";
            foreach ($this->errors as $error) {
                $text .= '- '.$error->md()."\r\n";
            }
            $text .= "\r\n";
        }

        $fileName = $this->makeFilename($this->path);
        file_put_contents($dir.$fileName.'.md', $text);
        return sprintf("[%s](%s)", basename($this->path), $fileName);
    }
}

class PDConstant
{
    const ELEM_NAME = 'constant';

    var $namespace; //string
    var $line; //string
    var $visibility; //string
    var $name; //string
    var $fullName; //string
    var $value; //string
    var $inheritedFrom; //string
    var $docBlock; //PDDocBlock

    public function __construct(PDElement $element) {
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->visibility = $element->getAttr('visibility', '');
        $this->name = '';
        $this->fullName = '';
        $this->value = '';
        $this->inheritedFrom = '';
        $this->docBlock = null;

        if (is_array($element->children) === true) {
            foreach ($element->children as $constChild) {
                if ($constChild->name === 'name') {
                    $this->name = $constChild->content;
                } else if ($constChild->name === 'full_name') {
                    $this->fullName = $constChild->content;
                } else if ($constChild->name === 'value') {
                    $this->value = $constChild->content;
                } else if ($constChild->name === 'inherited_from') {
                    $this->inheritedFrom = $constChild->content;
                } else if ($constChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($constChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = sprintf("### Constant %s (%s)\r\n\r\n", $this->name, $this->fullName);
        $text .= sprintf("Value `%s`\r\n", $this->value);
        if ($this->inheritedFrom !== '') {
            $text .= sprintf("Inherited from %s\r\n", $this->inheritedFrom);
        }
        $text .= "\r\n";
        if ($this->docBlock !== null) {
            $text .= $this->docBlock->md();
        }
        return $text;
    }
}

class PDDocBlock
{
    const ELEM_NAME = 'docblock';

    var $line; //string
    var $description; //string
    var $longDescription; //string
    var $tags; //PDTag[]

    public function __construct(PDElement $element) {
        $this->line = $element->getAttr('line', '');
        $this->description = '';
        $this->longDescription = '';
        $this->tags = [];

        if (is_array($element->children) === true) {
            foreach ($element->children as $constChild) {
                if ($constChild->name === 'description') {
                    $this->description = $constChild->content;
                } else if ($constChild->name === 'long-description') {
                    $this->longDescription = $constChild->content;
                } else if ($constChild->name === 'tags' && is_array($constChild->children) === true) {
                    foreach ($constChild->children as $tagsChild) {
                        $this->tags[] = new PDTag($tagsChild);
                    }
                }
            }
        }
    }

    public function md(): string
    {
        $text = sprintf("%s\r\n\r\n", $this->description);
        if ($this->longDescription !== '') {
            $text .= sprintf("%s\r\n\r\n", $this->longDescription);
        }
        if (count($this->tags) > 0) {
            foreach ($this->tags as $tag) {
                $text .= "- ".$tag->md()."\r\n";
            }
            $text .= "\r\n";
        }
        return $text;
    }
}

class PDTag
{
    const ELEM_NAME = 'tag';

    var $name;
    var $description;
    var $link;
    var $version;
    var $variable;
    var $method;
    var $type;

    public function __construct(PDElement $element) {
        $this->name = $element->getAttr('name', '');
        $this->description = $element->getAttr('description', '');
        $this->link = $element->getAttr('link', '');
        $this->version = $element->getAttr('version', '');
        $this->variable = $element->getAttr('variable', '');
        $this->method = $element->getAttr('method_name', '');
        $this->type = $element->getAttr('type', '');
    }

    public function md(): string
    {
        $text = sprintf("Tag <q>%s</q> = %s", $this->name, $this->description);
        return $text;
    }
}

class PDFunction
{
    const ELEM_NAME = 'function';

    var $namespace;
    var $line;
    var $package;
    var $returnByReference;
    var $name;
    var $fullName;
    var $docBlock;
    var $arguments;

    public function __construct(PDElement $element) {
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->package = $element->getAttr('package', '');
        $this->returnByReference = $element->getAttr('returnByReference', '');
        $this->name = '';
        $this->fullName = '';
        $this->docBlock = null;
        $this->arguments = [];

        if (is_array($element->children) === true) {
            foreach ($element->children as $funcChild) {
                if ($funcChild->name === 'name') {
                    $this->name = $funcChild->content;
                } else if ($funcChild->name === 'full_name') {
                    $this->fullName = $funcChild->content;
                } else if ($funcChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($funcChild);
                } else if ($funcChild->name === PDArgument::ELEM_NAME) {
                    $this->arguments[] = new PDArgument($funcChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = sprintf("### Function %s (%s)\r\n\r\n", $this->name, $this->fullName);
        if ($this->docBlock !== null) {
            $text .= $this->docBlock->md();
        }
        if (count($this->arguments) > 0) {

            $text .= "#### Arguments\r\n\r\n";
            foreach ($this->arguments as $argument) {
                $text .= "- ".$argument->md()."\r\n";
            }
            $text .= "\r\n";
        }
        return $text;
    }
}

class PDArgument
{
    const ELEM_NAME = 'argument';

    var $line;
    var $byReference;
    var $name;
    var $default;
    var $type;

    public function __construct(PDElement $element) {
        $this->line = $element->getAttr('line', '');
        $this->byReference = $element->getAttr('by_reference', '');
        $this->name = '';
        $this->default = '';
        $this->type = '';

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
    }

    public function md(): string
    {
        $text = sprintf('Arg <q>%s</q> type <q>%s</q> (default `%s`)', $this->name, $this->type, $this->default);
        return $text;
    }
}

class PDInterface
{
    const ELEM_NAME = 'interface';

    var $namespace;
    var $line;
    var $package;
    var $name;
    var $fullName;
    var $docBlock;
    var $extends;
    var $constants;
    var $methods;

    public function __construct(PDElement $element) {
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->package = $element->getAttr('package', '');
        $this->name = '';
        $this->fullName = '';
        $this->docBlock = null;
        $this->extends = [];
        $this->constants = [];
        $this->methods = [];

        if (is_array($element->children) === true) {
            foreach ($element->children as $funcChild) {
                if ($funcChild->name === 'name') {
                    $this->name = $funcChild->content;
                } else if ($funcChild->name === 'full_name') {
                    $this->fullName = $funcChild->content;
                } else if ($funcChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($funcChild);
                } else if ($funcChild->name === 'extends') {
                    $this->extends[] = $funcChild->content;
                } else if ($funcChild->name === PDConstant::ELEM_NAME) {
                    $this->constants[] = new PDConstant($funcChild);
                } else if ($funcChild->name === PDMethod::ELEM_NAME) {
                    $this->methods[] = new PDMethod($funcChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = '';
        return $text;
    }
}

class PDClass
{
    const ELEM_NAME = 'class';

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

    public function __construct(PDElement $element) {
        $this->final = $element->getAttr('final', '');
        $this->abstract = $element->getAttr('abstract', '');
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->name = '';
        $this->fullName = '';
        $this->docBlock = null;
        $this->extends = [];
        $this->implements = [];
        $this->constants = [];
        $this->properties = [];
        $this->methods = [];

        if (is_array($element->children) === true) {
            foreach ($element->children as $classChild) {
                if ($classChild->name === 'name') {
                    $this->name = $classChild->content;
                } else if ($classChild->name === 'full_name') {
                    $this->fullName = $classChild->content;
                } else if ($classChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($classChild);
                } else if ($classChild->name === 'extends') {
                    $this->extends[] = $classChild->content;
                } else if ($classChild->name === 'implements') {
                    $this->implements[] = $classChild->content;
                } else if ($classChild->name === PDConstant::ELEM_NAME) {
                    $this->constants[] = new PDConstant($classChild);
                } else if ($classChild->name === PDProperty::ELEM_NAME) {
                    $this->properties[] = new PDProperty($classChild);
                } else if ($classChild->name === PDMethod::ELEM_NAME) {
                    $this->methods[] = new PDMethod($classChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = sprintf("### Class %s (%s)\r\n\r\n", $this->name, $this->fullName);
        return $text;
    }
}

class PDProperty
{
    const ELEM_NAME = 'property';

    var $namespace;
    var $line;
    var $visibility;
    var $name;
    var $fullName;
    var $default;
    var $inheritedFrom;
    var $docBlock;

    public function __construct(PDElement $element) {
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->visibility = $element->getAttr('visibility', '');
        $this->name = '';
        $this->fullName = '';
        $this->default = '';
        $this->inheritedFrom = '';
        $this->docBlock = null;

        if (is_array($element->children) === true) {
            foreach ($element->children as $propChild) {
                if ($propChild->name === 'name') {
                    $this->name = $propChild->content;
                } else if ($propChild->name === 'full_name') {
                    $this->fullName = $propChild->content;
                } else if ($propChild->name === 'default') {
                    $this->default = $propChild->content;
                } else if ($propChild->name === 'inherited_from') {
                    $this->inheritedFrom = $propChild->content;
                } else if ($propChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($propChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = '';
        return $text;
    }
}

class PDMethod
{
    const ELEM_NAME = 'method';

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

    public function __construct(PDElement $element) {
        $this->final = $element->getAttr('final', '');
        $this->abstract = $element->getAttr('abstract', '');
        $this->static = $element->getAttr('static', '');
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->returnByReference = $element->getAttr('returnByReference', '');
        $this->name = '';
        $this->fullName = '';
        $this->value = '';
        $this->inheritedFrom = '';
        $this->arguments = [];
        $this->docBlock = null;

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
                    $this->docBlock = new PDArgument($methodChild);
                } else if ($methodChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($methodChild);
                }
            }
        }
    }

    public function md(): string
    {
        $text = '';
        return $text;
    }
}

class PDTrait
{
    const ELEM_NAME = 'trait';

    var $namespace;
    var $line;
    var $name;
    var $fullName;
    var $docBlock;
    var $properties;
    var $methods;

    public function __construct(PDElement $element) {
        $this->namespace = $element->getAttr('namespace', '');
        $this->line = $element->getAttr('line', '');
        $this->name = '';
        $this->fullName = '';
        $this->docBlock = null;
        $this->properties = [];
        $this->methods = [];

        if (is_array($element->children) === true) {
            foreach ($element->children as $traitChild) {
                if ($traitChild->name === 'name') {
                    $this->name = $traitChild->content;
                } else if ($traitChild->name === 'full_name') {
                    $this->fullName = $traitChild->content;
                } else if ($traitChild->name === PDDocBlock::ELEM_NAME) {
                    $this->docBlock = new PDDocBlock($traitChild);
                } else if ($traitChild->name === PDProperty::ELEM_NAME) {
                    $this->properties[] = new PDProperty($traitChild);
                } else if ($traitChild->name === PDMethod::ELEM_NAME) {
                    $this->properties[] = new PDMethod($traitChild);
                }
            }
        }

    }

    public function md(): string
    {
        $text = '';
        return $text;
    }
}

class PDMarker
{
    var $type;
    var $line;
    var $message;

    public function __construct(PDElement $element) {
        $this->type = $element->name;
        $this->line = $element->getAttr('line', '');
        $this->message = $element->content;
    }

    public function md(): string
    {
        $text = sprintf('Marker `%s` on line %s: <q>%s</q>', $this->type, $this->line, $this->message);
        return $text;
    }
}

class PDError
{
    const ELEM_NAME = 'error';

    var $code;
    var $line;

    public function __construct(PDElement $element) {
        $this->code = $element->content;
        $this->line = $element->getAttr('line', '');
    }

    public function md(): string
    {
        $text = sprintf('Error `%s` on line %s', $this->code, $this->line);
        return $text;
    }
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
        $project->md(__DIR__ . '\\..\\..\\mass-wiki\\phpdoc\\');
    }
}

class PDGenerator
{

}

$pdmdxparser = new PDParser;
$pdmdxparser->run();
