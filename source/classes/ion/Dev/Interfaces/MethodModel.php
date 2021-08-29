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
    
    private const PARAMETER_LINEBREAK_THRESHOLD = 3;
    
    private $name;
    private $static;
    private $parameters;
    private $returnType;
    private $byReference;
    
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
        $this->setStatic($static);
        $this->setReturnsByReference(false);
        
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
    
    public function hasReturnType(): bool {
        
        return ($this->getReturnType() !== null);
    }
    
    public function setStatic(bool $static): self {
        
        $this->static = $static;
        return $this;
    }
    
    public function getStatic(): bool {
        
        return $this->static;
    }
    
    public function isStatic(): bool {
        
        return ($this->getStatic() === true);
    }
    
    public function addParameter(MethodParameterModel $parameter): self {
        
        $this->parameters[$parameter->getName()] = $parameter;
        return $this;
    }
    
    public function getParameters(): array {
        
        return $this->parameters;
    }
    
    public function hasParameters(): bool {
        
        return (count($this->getParameters()) > 0);
    }
    
    public function getParameter(string $name): MethodParameterModel {
        
        return $this->parameters[$name];
    }
    
    public function setReturnsByReference(bool $byReference = null): self {
        
        $this->byReference = $byReference;
        return $this;
    }
    
    public function getReturnsByReference(): bool {
        
        return $this->byReference;
    }
    
    public function returnsByReference(): bool {
        
        return ($this->getReturnsByReference() === true);
    }         
    
    public function toString(): string {
        
        $php = "";
        
        if($this->hasDoc()) {
                    
            //var_Dump(static::trim($this->getDoc()));            
            
            $php .= "{$this->getDoc()}\n\n";
        }
        
        if($this->isStatic()) {
            
            $php .= "static ";
        }
        
        $parameters = "";
        
        if($this->hasParameters()) {
            
            $tmp = [];
            
            foreach($this->getParameters() as $name => $param) {
                
                $tmp[] = $param->toString();
            }
            
            $parameters = implode("," . (count($tmp) > static::PARAMETER_LINEBREAK_THRESHOLD ? "\n" : " "), $tmp);
            
            if(count($tmp) > static::PARAMETER_LINEBREAK_THRESHOLD) {

                $parameters = "\n\n" . static::indent("{$parameters}") . "\n\n";
            }
        }
        
        $php .= "function ";
        
        if($this->returnsByReference()) {
            
            $php .= "&";
        }
        
        $php .= "{$this->getName()}({$parameters})";
        
        if($this->hasReturnType()) {
            
            $php .= ": {$this->getReturnType()}";
            
        }
        
        return "{$php};";
    }
    
}
