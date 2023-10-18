<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of MethodParameterModel
 *
 * @author Justus
 */

class MethodParameterModel extends NodeModel {
    
    public const DEFAULT_TYPE_STRING = "string";
    public const DEFAULT_TYPE_CONST = "const";
    public const DEFAULT_TYPE_NULL = "null";
    public const DEFAULT_TYPE_ARRAY = "array";
    public const DEFAULT_TYPE_SCALAR = "scalar";
    public const DEFAULT_TYPE_CLASS = "class";
    
    private $name;
    private $type;
    private $default;
    private $variadic;
    private $defaultType;
    private $byReference;
    
    public function __construct(
            
        string $name,
        TypeModel $type = null,
        string $default = null,
        bool $defaultType = false,
        bool $byReference = false,
        bool $variadic = false,        
        string $doc = null
            
    ) {
        
        parent::__construct($doc);
        
        $this->setName($name);
        $this->setType($type);        
        $this->setDefault($default, $defaultType);
        $this->setByReference($byReference);
        $this->setVariadic($variadic);
    }
    
    public function setName(string $name): self {
        
        $this->name = $name;
        return $this;
    }
    
    public function getName(): string {
        
        return $this->name;
    }    
    
    public function setType(TypeModel $type = null): self {
        
        $this->type = $type;
        return $this;
    }
    
    public function getType(): ?TypeModel {
        
        return $this->type;
    }    
    
    public function hasType(): bool {
        
        return ($this->getType() !== null);
    }
    
    public function setDefault(string $default = null, string $defaultType = null): self {
        
        $this->default = $default;
        $this->defaultType = $defaultType;
        return $this;
    }
    
    public function getDefault(): ?string {
        
        return $this->default;
    }
    
    public function hasDefault(): bool {
        
        return ($this->getDefault() !== null);
    }  
    
    public function getDefaultType(): ?string {
        
        return $this->defaultType;
    }
    
    public function isDefaultAString(): bool {
        
        return ($this->getDefaultType() === static::DEFAULT_TYPE_STRING);
    }
    
    public function isDefaultAnArray(): bool {
        
        return ($this->getDefaultType() === static::DEFAULT_TYPE_ARRAY);
    }    
    
    public function isDefaultAConstant(): bool {
        
        return ($this->getDefaultType() === static::DEFAULT_TYPE_CONST);
    }    
    
    public function isDefaultNull(): bool {
        
        return ($this->getDefaultType() === static::DEFAULT_TYPE_NULL);
    }        
    
    public function setByReference(bool $byReference = null): self {
        
        $this->byReference = $byReference;
        return $this;
    }
    
    public function getByReference(): bool {
        
        return $this->byReference;
    }
    
    public function isByReference(): bool {
        
        return ($this->getByReference() === true);
    }        
    
    
    public function setVariadic(string $variadic = null): self {
        
        $this->variadic = $variadic;
        return $this;
    }
    
    public function getVariadic(): bool {
        
        return $this->variadic;
    }
    
    public function isVariadic(): bool {
        
        return ($this->getVariadic() === true);
    }      
    
    public function toString(): string {
        
        $php = "";
        
        if($this->hasType()) {

            $php .= "{$this->getType()} ";
        }     
        
        if($this->isByReference()) {
            
            $php .= "&";
        }

        if($this->isVariadic()) {
            
            $php .= "...";        
        }
 
        $php .= "\${$this->getName()}";        
        
        if($this->hasDefault()) {
            
            $php .= " = ";
            
            if($this->isDefaultAString()) {
                            
                $php .= "\"{$this->getDefault()}\"";
            }
            else if($this->isDefaultAnArray()) {
            
                $php .= "[{$this->getDefault()}]";            
            }
            else if($this->isDefaultAConstant()) {
            
                $php .= "{$this->getDefault()}";            
            }
            else if($this->isDefaultNull()) {
                            
                $php .= "null";
                
            } else {
                
                $php .= "{$this->getDefault()}";
            }
        }
        
        return $php;
    }    
}
