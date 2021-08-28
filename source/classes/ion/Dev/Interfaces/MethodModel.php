<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of MethodModel
 *
 * @author Justus
 */

class MethodModel extends NodeModel {
    
    
    private $name;
    private $static;
    private $parameters;
    private $returnType;
    
    public function __construct(
            
        string $name,
        array $parameters = [],
        NameModel $return = null,
        bool $static = false,
        string $doc = null
            
    ) {
        
        parent::__construct($doc);
        
        $this->setName($name);
        $this->setReturnType($return);
        
        $this->parameters = $parameters;
    }
    
    public function setName(string $name): self {
        
        $this->name = $name;
        return $this;
    }
    
    public function getName(): string {
        
        return $this->name;
    }    
    
    public function setReturnType(TypeModel $returnType = null): self {
        
        $this->returnType = $returnType;
        return $this;
    }
    
    public function getReturnType(): ?TypeModel {
        
        return $this->returnType;
    }        
    
    public function setStatic(bool $static): self {
        
        $this->static = $static;
        return $this;
    }
    
    public function getStatic(): bool {
        
        return $this->static;
    }
    
    public function addParameter(MethodParameterModel $parameter): self {
        
        $this->parameters[$parameter->getName()] = $parameter;
        return $this;
    }
    
    public function getParameters(): array {
        
        return $this->parameters;
    }
    
    public function getParameter(string $name): MethodParameterModel {
        
        return $this->parameters[$name];
    }
    
    public function toString(): string {
        
        return "";
    }
    
}
