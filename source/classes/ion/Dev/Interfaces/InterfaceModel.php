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
        array $suffixesToIgnore = [],
        array $namespaces = null
            
    ): self {
        
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($data);
        
        if($ast === null) {
            
            throw new Exception("Could not create PHP parser object.");
        }
        
        $traverser = new NodeTraverser();

        $model = new InterfaceModel(
                
            $templates, 
            $prefixesToStrip, 
            $suffixesToStrip, 
            $prefixesToIgnore, 
            $suffixesToIgnore,
            $namespaces
        );
        
        $traverser->addVisitor(new NodeVisitor($model));
        
        $ast = $traverser->traverse($ast);

        return $model;        
    }
    
    private $references = [];
    private $traits = [];
    private $traitInterfaces = [];
    private $interfaces = [];
    private $methods = [];
    private $parent = null;
    private $structName = null;
    private $structType = null;
    
    private $templates = [];
    private $prefixesToStrip = [];
    private $suffixesToStrip = [];
    private $prefixesToIgnore = [];
    private $suffixesToIgnore = [];    
    private $namespaces = null;

    public function __construct(
     
        array $templates,
        array $prefixesToStrip = [],
        array $suffixesToStrip = [],
        array $prefixesToIgnore = [],
        array $suffixesToIgnore = [],
        array $namespaces = null,
        string $doc = null
            
    ) {

        parent::__construct($doc);
        
        $this->structType = StructType::UNKNOWN;
        $this->templates = $templates;
        $this->prefixesToStrip = $prefixesToStrip;
        $this->suffixesToStrip = $suffixesToStrip;
        $this->prefixesToIgnore = $prefixesToIgnore;
        $this->suffixesToIgnore = $suffixesToIgnore;
        $this->namespaces = $namespaces;
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
    
    public function setStructType(int $structType): self {
        
        $this->structType = $structType;
        return $this;
    }
    
    public function getStructType(): int {
        
        return $this->structType;
    }
    
    public function isStructAClass(): bool {
        
        return ($this->getStructType() === StructType::CLASS_);
    }
    
    public function isStructATrait(): bool {
        
        return ($this->getStructType() === StructType::TRAIT_);        
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
    
    public function getTraitInterfaces(): array {
        
        return $this->traitInterfaces;
    }
    
    public function getInterfaces(): array {
        
        return $this->interfaces;
    }
    
    public function getMethods(): array {
        
        return $this->methods;
    }
    
    public function getNamespaces(): array {
        
        return $this->namespaces;
    }
    
    public function addReference(NameModel $name, bool $increaseCount = false): self {
  
        if(!$this->hasReference($name)) {

            if((!$name->hasNamespace() && $this->getStructName() !== null) && (!$name->isPhpStruct())) {
                
                $this->references[$name->getName()] = new UseNameModel($name, ($name->isAbsolute() ? [] : $this->getStructName()->getNamespaceParts()));

            } else {
                
                $this->references[$name->getName()] = new UseNameModel($name);            
            }
        }
        
        if($this->hasReference($name) && $increaseCount) {
            
            $this->getReference($name)->increaseReferences();
        }
        
        return $this;
    }
    
    protected function clearReferences(bool $countOnly = false): void {
        
        if($countOnly) {
            
            foreach($this->references as $key => $ref) {
                
                $ref->clearReferencesCount();
            }
            
            return;
        }
        
        $this->references = [];
        return;
    }

    public function hasReference(NameModel $name): bool {
        
        return (array_key_exists($name->getModifiedName()->getName(), $this->references));
    }

    
    public function getReference(NameModel $name): ?NameModel {
        
        if(!$this->hasReference($name)) {
            
            return null;
        }
        
        return $this->references[$name->getModifiedName()->getName()];
    }
    
    public function setParent(NameModel $parent = null): self {

        if($parent !== null) {
            
            if($this->hasReference($parent)) {
                
                $parent = $this->getReference($parent);                
            }
            
            $this->addReference($parent, false);
            
            foreach($parent->getInterfaceVariations(

                    $this->templates, 
                    $this->prefixesToStrip, 
                    $this->suffixesToStrip, 
                    $this->prefixesToIgnore, 
                    $this->suffixesToIgnore

            ) as $interface) {

                $this->addReference($interface, false);
            }             
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
    
    public function addTrait(NameModel $name): self {
        
        if(!array_key_exists($name->getName(), $this->traits)) {
            
            $this->traits[$name->getName()] = $name;
        }
        
        foreach($name->getInterfaceVariations(
                
                $this->templates, 
                $this->prefixesToStrip, 
                $this->suffixesToStrip, 
                $this->prefixesToIgnore, 
                $this->suffixesToIgnore
                
        ) as $ref) {
        
            $tmp = $ref->getName();
            
            if(array_key_exists($tmp, $this->traitInterfaces)) {

                return $this;
            }
            
            $this->addReference($ref, false);

            $this->traitInterfaces[$tmp] = $ref;
        }
        
        return $this;        
    }
    
    public function addInterface(NameModel $name): self {
        
        //foreach(array_merge([ $name ], $name->getInterfaceVariations($this->templates)) as $ref) {        
        
        foreach(array_merge([ $name ]) as $ref) {        
            
            if(array_key_exists($ref->getModifiedName()->getName(), $this->interfaces)) {

                return $this;
            }

            $this->addReference($ref, false);

            $this->interfaces[$ref->getModifiedName()->getName()] = $ref;
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

    public function generate(string $interfaceName, array &$memory, int $templateIndex = 0): ?string {
        
        if(!$this->hasStructName()) {
            
            return null;
        }
        
        $this->clearReferences(true);
        
        $primary = ($templateIndex === 0);
        $template = $this->templates[$templateIndex];

        if($this->isStructAClass() && !array_key_exists($interfaceName, $this->interfaces)) {
            
            return null;
        }
        
        $php = "";

        if($this->hasDoc()) {

            $php .= "\n{$this->getDoc()}\n";
        }

        $php .= "interface {$interfaceName}";

        $methods = "";

        $extends = [];

 //       var_dump($this->hasParent());
        
        if($this->hasParent() && !$this->getParent()->isPhpClass() && ($this->getParent()->getNamespace() === $this->getStructName()->getNamespace())) {

//            if($this->getStructName()->getName() == "AbstractLogger") {
                
//                ($this->getParent()->getNamespace() !== $this->getStructName()->getNamespace())
                
//                var_dump($this->getParent()->getNamespace());
//                var_dump($this->getStructName()->getNamespace());
//                exit;
//            }
            
            $name = $this
                    
                ->getParent()
                ->asInterfaceName($template);

            if(!in_array($name->getName(), $extends) && !in_array($name->getName(), $memory)) {

                $extends[] = $name->getName();
                
                $this->addReference($name, true);
            }
        }

        foreach($this->getTraits() as $trait) {                
             
            // First strip the trait prefixes / suffixes
            
            $name = $trait->getModifiedName(
                    
                $this->prefixesToStrip, 
                $this->suffixesToStrip, 
                $this->prefixesToIgnore, 
                $this->suffixesToIgnore
            );
                        
            $name = $name->asInterfaceName($template);
                        
            //$name = $name->getInterfaceVariations([ $template ])[0];
            
            if(in_array($name->getName(), $extends) || in_array($name->getName(), $memory)) {

                continue;
            }                        

            $extends[] = $name->getName();
            
            $this->addReference($name, true);
        }     

        foreach($this->getInterfaces() as $key => $interface) {
                 
            $name = $interface;
            
            if(!$primary) {
                
                continue;
            }
            
            if(!$primary && $interface->getName() !== $interfaceName) {
            
                $name = $name
                        ->asInterfaceName($template);
                        //->getInterfaceVariations([ $template ])[0];
            }
            
            if(in_array($name->getName(), $extends) || in_array($name->getName(), $memory)) {

                continue;
            }                

            if($name->getName() === $interfaceName) {

                continue;
            }

//            $this->addReference($name, true);
            
//            if($interfaceName == "IClassname") {
//                
//                var_Dump($interface);
//                var_dump($name);
//                exit;
//            }
            
            $extends[] = $name->getName();
            
            $this->addReference($name, true);
        }                

        $templates = $this->templates;
        
        if(!$primary && count($templates) > 1) {

            $templates = array_slice($templates, 1);
        }        
        
        if(count($this->templates) > 0) {

            $variations = $this
                ->getStructName()
                ->getInterfaceVariations(

                    $templates,
                    $this->prefixesToStrip, 
                    $this->suffixesToStrip, 
                    $this->prefixesToIgnore, 
                    $this->suffixesToIgnore
                );
            
            foreach($variations as $variationInterfaceName) {                
            
                if(!$primary) {

                    continue;
                }                

                if(in_array($variationInterfaceName->getName(), $extends) || $variationInterfaceName->getName() === $interfaceName || in_array($variationInterfaceName->getName(), $memory)) {

                    continue;
                }                    

                $extends[] = $variationInterfaceName->getName();
                
                $this->addReference($variationInterfaceName, true);
            }        
        }        

        if($primary) {            
            
            foreach($this->getMethods() as $key => $method) {

                $methods .= "{$method->toString()}\n\n";
                
                foreach($method->getParameters() as $pName => $parameter) {
                    
                    $type = $parameter->getType();                                 
                    
                    if($type === null || ($type !== null && $type->getName()->isPhpType())) {
                        
                        continue;
                    }

                    $this->addReference($type->getName(), true);
                }
                
                if($method->hasReturnType() && !$method->getReturnType()->getName()->isPhpType()) {
                    
                    $this->addReference($method->getReturnType()->getName(), true);
                }
            }            
        }        
        
        if(!empty($methods)) {
            
            $methods = static::indent($methods);
        }
        else {
            
            $methods = static::indent(static::EMPTY_METHODS_COMMENT) . "\n\n";
        }
 
        $php .= (empty($extends) ? "" : " extends ");

        if(count($extends) > self::EXTENDS_WRAP_THRESHOLD) {

            $php .= "\n\n" . static::indent(implode(",\n", $extends)) . "\n\n";

        } else {

            $php .= implode(", ", $extends);
        }
        
        // We process the use references last
        
        $header = "<?php\n\n";
        
        if($this->getStructName()->hasNamespace()) {
            
            $header .= "namespace {$this->getStructName()->getNamespace()};\n" . ($this->hasReferences() ? "\n" : "");
        }

        $refCnt = 0;
        
        foreach($this->getReferences() as $key => $reference) {

            if(!$reference->hasReferences()) {

                continue;
            }

            if($reference->getName() === $interfaceName) {

                continue;
            }

            $header .= "{$reference}\n";
            
            $refCnt++;
        }        
        
        if($refCnt > 0) {
            
            $php = "\n{$php}";
        }
        
        $php = "{$header}{$php} {\n\n{$methods}}\n";
        
        
        $memory[] = $interfaceName;        
        
        return $php;
    }
    
    
}
