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
        
        return "";
    }    
}
