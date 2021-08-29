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
class NameModel {
    
    public static function getFromParts(array $parts, bool $hasName = true): self {
        
        if(count($parts) <= 1) {
            
            if(count($parts) === 0) {

                return new static();
            }            
            
            if($hasName) {
                
                return new static(
                        
                    null,
                    $parts[0]
                );  
            }
            
            return new static(

                $parts,
                null                    
            );  
        }
        
        if($hasName) {

            return new static(

                array_slice($parts, 0, count($parts) - 1),
                $parts[count($parts) - 1]
            );  
        }

        return new static(

            $parts,
            null                    
        );     
    }
    
    private $namespace = null;
    private $name = null;
    private $absolute = true;
    
    public function __construct(array $namespace = null, string $name = null, bool $absolute = true) {
        
        $this->namespace = $namespace;
        
        if(!empty($name)) {
            
            $this->setName($name);
        }
    }
    
    public function setName(string $name): self {
        
        $this->name = $name;
        return $this;
    }
    
    public function getName(): string {
        
        return $this->name;
    }
    
    public function setAbsolute(bool $absolute = true): self {
        
        $this->absolute = $absolute;
        return $this;
    }
    
    public function getAbsolute(): bool {
        
        return $this->absolute;
    }
    
    public function isAbsolute(): bool {
        
        return $this->getAbsolute();
    }
    
    public function setNamespaceParts(array $namespace = null): self {
        
        $this->namespace = $namespace;
        return $this;
    }
    
    public function getNamespaceParts(): ?array {
        
        return $this->namespace;
    }    
    
    public function getNamespace(bool $nullIfEmpty = false): ?string {
        
        
        if(empty($this->namespace) || (is_countable($this->namespace) && count($this->namespace) < 1)) {
            
            return ($nullIfEmpty ? null : "");
        }
        
        return implode("\\", $this->namespace);
    }
    
    public function hasNamespace(): bool {
        
        return (!empty($this->getNameSpace(true)));
    }
    
    //FIXME
    public function asInterfaceName(string $template = null): string {
        
        return $this->getName();
    }
    
    //FIXME
    public function getClassInterfaceVariations(array $templates = null): array {
        
        return [ $this ];
    }
    
    //FIXME
    public function getTraitInterfaceVariations(array $prefixes = null, array $suffixes = null): array {
        
        return [ $this ];
    }
    
    public function toString(): string {
        
        if($this->hasNamespace()) {
        
            return ($this->isAbsolute() ? "\\" : "") . "{$this->getNamespace()}\\{$this->getName()}";
        }
        
        return ($this->isAbsolute() ? "\\" : "") . $this->getName();
    }
    
    public function __toString() {
        
        return $this->toString();
    }
}
