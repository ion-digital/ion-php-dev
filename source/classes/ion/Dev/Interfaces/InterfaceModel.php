<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

use \PhpParser\ParserFactory;
use \PhpParser\NodeTraverser;

/**
 * Description of InterfaceModel
 *
 * @author Justus
 */

class InterfaceModel extends NodeModel {
    
    private const EMPTY_METHODS_COMMENT = "// No methods found!";

    public static function parseData(
            
        string $data,
        array $templates,
        array $prefixesToStrip = [],
        array $suffixesToStrip = []
            
    ): self {
        
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($data);
        
        if($ast === null) {
            
            throw new Exception("Could not create PHP parser object.");
        }
        
        $traverser = new NodeTraverser();

        $model = new InterfaceModel($templates, $prefixesToStrip, $suffixesToStrip);
        
        $traverser->addVisitor(new NodeVisitor($model));
        
        $ast = $traverser->traverse($ast);

        return $model;        
    }
    
    private $references = [];
    private $traits = [];
    private $interfaces = [];
    private $methods = [];
    private $parent = null;
    private $structName = null;
    
    private $templates = [];
    private $prefixes = [];
    private $suffixes = [];

    public function __construct(
     
        array $templates,
        array $prefixesToStrip = [],
        array $suffixesToStrip = [],
        string $doc = null
            
    ) {

        parent::__construct($doc);
        

        $this->templates = $templates;
        $this->prefixes = $prefixesToStrip;
        $this->suffixes = $suffixesToStrip;
    }

    public function setStructName(NameModel $name): self {
        
        $this->structName = $name;
        return $this;
    }
    
    public function getStructName(): ?NameModel {
        
        return $this->structName;
    }
    
    public function hasStructName(): bool {
        
        return ($this->getStructName() !== null);
    }
    
    public function setParent(NameModel $parent = null): self {
        
        $this->parent = $parent;
        return $this;
    }
    
    public function getParent(): ?NameModel {
        
        return $this->parent;
    }
    
    public function hasParent(): bool {
        
        return ($this->parent !== null);
    }
    
    public function getReferences(): array {
        
        return $this->references;
    }
    
    public function getTraits(): array {
        
        return $this->traits;
    }
    
    public function getInterfaces(): array {
        
        return $this->interfaces;
    }
    
    public function getMethods(): array {
        
        return $this->methods;
    }
    
    public function addReference(NameModel $name): self {
        
        if(array_key_exists($name->getName(), $this->references)) {

            return $this;
        }

        $this->references[$name->getName()] = new UseNameModel($name);
        
        return $this;
    }
    
    public function addTrait(NameModel $name): self {
        
        foreach($name->getTraitInterfaceVariations($this->prefixes, $this->suffixes) as $ref) {
        
            if(array_key_exists($ref->getName(), $this->traits)) {

                return $this;
            }

            $this->addReference($ref);

            $this->traits[$ref->getName()] = $ref;
        }
        return $this;        
    }
    
    public function addInterface(NameModel $name): self {
        
        foreach(array_merge([ $name ], $name->getClassInterfaceVariations($this->templates)) as $ref) {        
            
            if(array_key_exists($ref->getName(), $this->interfaces)) {

                return $this;
            }

            $this->addReference($ref);

            $this->interfaces[$ref->getName()] = $ref;
        }
        
        return $this;        
    }
    
    public function addMethod(MethodModel $method): self {
        
        $this->methods[$method->getName()] = $method;
        return $this;
    }
    
    public function toString(): string {
        
        return $this->generate($this->getStructName()->asInterfaceName());
    }
    
    public function generate(string $interfaceName, bool $primary = true): string {
        
        if(!$this->hasStructName()) {
            
            return "";
        }
        
        $php = "<?php\n\n";
        
        if($this->getStructName()->hasNamespace()) {
            
            $php .= "namespace {$this->getStructName()->getNamespace()};\n\n";
        }
        
        foreach($this->getReferences() as $key => $reference) {
            
            $php .= "use {$reference};\n";
        }
        
        if($this->hasDoc()) {
            
            $php .= "\n{$this->getDoc()}\n";
        }
        
        $php .= "\ninterface {$interfaceName}";
        
        $extends = [];
        
        if($this->hasParent()) {
            
            $extends[] = $this->getParent()->asInterfaceName();
        }
        
        foreach($this->getInterfaces() as $key => $interface) {
            
            $extends[] = $interface->getName();
        }
        
        foreach($this->getTraits() as $key => $trait) {
            
            $extends[] = $trait->asInterfaceName();
        }        
        
        $methods = "";
        
        //$php .= "\n\n\n" . var_Export($this->getMethods(), true);
        
        foreach($this->getMethods() as $name => $method) {
            
            $methods .= "{$method->toString()}\n\n";
        }
        
        
        if(!empty($methods)) {
            
            $methods = static::indent($methods);
        }
        else {
            
            $methods = static::indent(static::EMPTY_METHODS_COMMENT);
        }
        
        $php .= (empty($extends) ? "" : " extends " . implode(", ", $extends)) . " {\n\n{$methods}}\n";
        
        //$php .= "\n\n\n" . var_Export($this, true);
        
        return $php;
    }
    
    
}
