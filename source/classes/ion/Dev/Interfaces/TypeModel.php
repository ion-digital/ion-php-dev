<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of NameModel
 *
 * @author Justus
 */
class TypeModel {
        
    public const TYPE_STRING = "string";
    public const TYPE_CONST = "const";
    public const TYPE_NULL = "null";
    public const TYPE_ARRAY = "array";
    public const TYPE_SCALAR = "scalar";
    public const TYPE_CLASS = "class";    
    public const TYPE_MIXED = "mixed";    
    
    private $name;
    private $nullable;
    
    public function __construct(
            
        NameModel $name = null,
        bool $nullable = false
            
    ) {
        
        $this->setName($name);
        $this->setNullable($nullable);
    }
    
    public function setName(NameModel $name = null): self {
        
        $this->name = $name;
        return $this;
    }
    
    public function getName(): ?NameModel {
        
        return $this->name;
    }    
    
    public function hasName(): bool {
        
        return ($this->getName() !== null);
    }
    
    public function setNullable(bool $nullable = false): self {
        
        $this->nullable = $nullable;
        return $this;
    }
    
    public function getNullable(): bool {
        
        return $this->nullable;
    }
    
    public function isNullable(): bool {
        
        return ($this->getNullable() === true);
    }
    
    //TODO
    public function getValueString(string $value = null): string {
        
        return "";
    }    
    
    public function toString(): string {
        
        if(!$this->hasName()) {
            
            return "";
        }
        
        if($this->getName()->isPhpType()) {
            
            $tmp = "{$this->getName()->getName()}";
            
        } else {
            
            $tmp = "{$this->getName()->getFullName()}";        
        }
        
        if($this->isNullable()) {
            
            return "?{$tmp}";
        }
        
        return $tmp;
    }    
    
    public function __toString(): string {
        
        return $this->toString();
    }
}
