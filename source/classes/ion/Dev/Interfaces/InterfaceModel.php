<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of InterfaceModel
 *
 * @author Justus
 */

class InterfaceModel extends NodeModel {
    

    private $references = [];
    private $traits = [];
    private $interfaces = [];
    private $methods = [];
    private $parent = null;
    private $name = null;

    public function __construct(string $doc = null) {

        parent::__construct($doc);
    }

    public function setName(NameModel $name): self {
        
        $this->name = $name;
        return $this;
    }
    
    public function getName(): ?NameModel {
        
        return $this->name;
    }
    
    public function hasName(): bool {
        
        return ($this->getName() !== null);
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
    
    public function addReference(NameModel $name): self {
        
        if(array_key_exists($name->getName(), $this->references)) {
            
            return $this;
        }
        
        $this->references[$name->getName()] = new UseNameModel($name);
        return $this;
    }
    
    public function addTrait(NameModel $name): self {
        
        if(array_key_exists($name->getName(), $this->traits)) {
            
            return $this;
        }
        
        $this->addReference($name);
        
        $this->traits[$name->getName()] = $name;
        return $this;        
    }
    
    public function addInterface(NameModel $name): self {
        
        if(array_key_exists($name->getName(), $this->interfaces)) {
            
            return $this;
        }
        
        $this->addReference($name);
        
        $this->interfaces[$name->getName()] = $name;
        return $this;        
    }
    
    public function addMethod(MethodModel $method): self {
        
        $this->methods[$method->getName()] = $method;
        return $this;
    }
    
    public function toString(): string {
        
        return var_export($this, true);
    }
    
    
}
