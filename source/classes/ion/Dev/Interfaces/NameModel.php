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
    
    public function __construct(array $namespace = null, string $name = null) {
        
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
    
    public function getAsBasename(array $templates = null, array $prefixes = null, array $suffixes = null): string {
        
        
    }
    
    public function getAsTraitName(bool $full = true): string {
        
    }
    
    public function getAsInterfaceName(bool $full = true): string {
        
    }
    
    
    public function toString(): string {
        
        return "";
    }
}
