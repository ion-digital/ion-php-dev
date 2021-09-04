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
    
    private const EMPTY_METHODS_COMMENT = "// No public methods!";
    private const EXTENDS_WRAP_THRESHOLD = 3;

    public static function parseData(
            
        string $data,
        array $templates,
        array $prefixesToStrip = [],
        array $suffixesToStrip = [],
        array $prefixesToIgnore = [],
        array $suffixesToIgnore = []
            
    ): self {
        
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($data);
        
        if($ast === null) {
            
            throw new Exception("Could not create PHP parser object.");
        }
        
        $traverser = new NodeTraverser();

        $model = new InterfaceModel($templates, $prefixesToStrip, $suffixesToStrip, $prefixesToIgnore, $suffixesToIgnore);
        
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
    private $prefixesToStrip = [];
    private $suffixesToStrip = [];
    private $prefixesToIgnore = [];
    private $suffixesToIgnore = [];    

    public function __construct(
     
        array $templates,
        array $prefixesToStrip = [],
        array $suffixesToStrip = [],
        array $prefixesToIgnore = [],
        array $suffixesToIgnore = [],            
        string $doc = null
            
    ) {

        parent::__construct($doc);
        

        $this->templates = $templates;
        $this->prefixesToStrip = $prefixesToStrip;
        $this->suffixesToStrip = $suffixesToStrip;
        $this->prefixesToIgnore = $prefixesToIgnore;
        $this->suffixesToIgnore = $suffixesToIgnore;        
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

        if($parent !== null) {
            
            if($this->hasReference($parent)) {
                
                $parent = $this->getReference($parent);                
            }
            
            $this->addReference($parent->asInterfaceName($this->templates[0]), true);
        }
        
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
    
    public function hasReferences(): bool {
        
        return (count($this->getReferences()) > 0);
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
    
    public function addReference(NameModel $name, bool $increaseCount = false): self {
  
        if(!$this->hasReference($name)) {

            if(!$name->hasNamespace() && $this->getStructName() !== null) {
                
                $this->references[$name->getModifiedName()] = new UseNameModel($name, $this->getStructName()->getNamespaceParts());

            } else {
                
                $this->references[$name->getModifiedName()] = new UseNameModel($name);            
            }
        }
        
        if($increaseCount) {
            
            $this->getReference($name)->increaseReferences();
        }
        
        return $this;
    }

    public function hasReference(NameModel $name): bool {
        
        return (array_key_exists($name->getModifiedName(), $this->references));
    }

    
    public function getReference(NameModel $name): ?NameModel {
        
        if(!$this->hasReference($name)) {
            
            return null;
        }
        
        return $this->references[$name->getModifiedName()];
    }
    
    
    public function addTrait(NameModel $name): self {
        
        foreach($name->getInterfaceVariations($this->templates, $this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore) as $ref) {
        
            if(array_key_exists($ref->getModifiedName(), $this->traits)) {

                return $this;
            }

            $this->addReference($ref, true);

            $this->traits[$ref->getModifiedName()] = $ref;
        }
        return $this;        
    }
    
    public function addInterface(NameModel $name): self {
        
        //foreach(array_merge([ $name ], $name->getInterfaceVariations($this->templates)) as $ref) {        
        
        foreach(array_merge([ $name ]) as $ref) {        
            
            if(array_key_exists($ref->getModifiedName(), $this->interfaces)) {

                return $this;
            }

            $this->addReference($ref, true);

            $this->interfaces[$ref->getModifiedName()] = $ref;
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
    
    public function generate(string $interfaceName, bool $primary = true): ?string {
        
        if(!$this->hasStructName()) {
            
            return null;
        }
        
        $php = "<?php\n\n";
        
        if($this->getStructName()->hasNamespace()) {
            
            $php .= "namespace {$this->getStructName()->getNamespace()};\n" . ($primary || !$this->hasReferences() ? "\n" : "");
        }
        
        if($primary) {

            foreach($this->getReferences() as $key => $reference) {

                if(!$reference->hasReferences()) {

                    continue;
                }

                if($reference->getName() === $interfaceName) {

                    continue;
                }

                $php .= "{$reference}\n";
            }

            if($this->hasDoc()) {

                $php .= "\n{$this->getDoc()}\n";
            }
        }
        
        $php .= "\ninterface {$interfaceName}";

        $methods = "";
        
        if($primary) {
        
            $extends = [];

            if($this->hasParent()) {

                $name = $this->getParent()->asInterfaceName($this->templates[0])->getModifiedName($this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore);

                if(!in_array($name, $extends)) {

                    $extends[] = $name;
                }
            }

            foreach($this->getTraits() as $key => $trait) {                

                $name = $trait->getModifiedName($this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore);

                if(in_array($name, $extends)) {

                    continue;
                }                        

                $extends[] = $name;
            }     

            foreach($this->getInterfaces() as $key => $interface) {

                $name = $interface->getModifiedName($this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore);

                if(in_array($name, $extends)) {

                    continue;
                }                
                
                if($name === $interfaceName) {
                    
                    continue;
                }

                $extends[] = $name;
            }                

            if(count($this->templates) > 0) {

                foreach($this->getStructName()->getInterfaceVariations($this->templates, $this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore) as $variationInterfaceName) {                

                    if($this->getStructName()->asInterfaceName($this->templates[0], $this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore)

                        ->getModifiedName($this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore) 
                            === $variationInterfaceName->getModifiedName($this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore)) {

                        continue;
                    }

                    $name = $variationInterfaceName->getModifiedName($this->prefixesToStrip, $this->suffixesToStrip, $this->prefixesToIgnore, $this->suffixesToIgnore);

                    if(in_array($name, $extends)) {

                        continue;
                    }                    

                    $extends[] = $name;
                }        
            }        

            foreach($this->getMethods() as $key => $method) {

                $methods .= "{$method->toString()}\n\n";
            }
        }        
        
        if(!empty($methods)) {
            
            $methods = static::indent($methods);
        }
        else {
            
            $methods = static::indent(static::EMPTY_METHODS_COMMENT) . "\n\n";
        }
        
        if($primary) {
        
            $php .= (empty($extends) ? "" : " extends ");

            if(count($extends) > self::EXTENDS_WRAP_THRESHOLD) {

                $php .= "\n\n" . static::indent(implode(",\n", $extends)) . "\n\n";

            } else {

                $php .= implode(", ", $extends);
            }
        }
        
        $php .= " {\n\n{$methods}}\n";

        return $php;
    }
    
    
}
