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
    
    public function getFullName(): string {
        
        if($this->hasNamespace()) {
        
            return ($this->isAbsolute() ? "\\" : "") . "{$this->getNamespace()}\\{$this->getName()}";
        }
        
        return ($this->isAbsolute() ? "\\" : "") . $this->getName();        
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
    
    protected function createNew(string $structName): self {
        
        $obj = clone $this;
        
        $obj->setName($structName);
        
        return $obj;
    }
    
    //FIXME
    public function asInterfaceName(string $template = null): string {
        
//                foreach($prefixesToStrip as $prefix) {
//
//                    if(!preg_match("/^({$prefix})/", $tmpFn)) {
//
//                        continue;
//                    }
//
//                    $tmpFn = preg_replace("/^({$prefix})/", '', $tmpFn, 1);  
//                    break;
//                }
//                
////                var_dump($classFn);
//
//                foreach($suffixesToStrip as $suffix) {
//
//                    if(!preg_match("/({$suffix})\$/", $tmpFn)) {
//
//                        continue;
//                    }
//
//                    $tmpFn = preg_replace("/({$suffix})\$/", '', $tmpFn, 1);
//                    break;
//                }           
        
        return $this->getName();
    }
    
    private static function applyTemplate(string $structName, string $template): string {
        
        return str_replace("*", $structName, $template);
    }
    
    //FIXME
    public function getClassInterfaceVariations(array $templates = null): array {
        
        $result = [];
        
        foreach($templates as $template) {
            
            $result[] = static::createNew(static::applyTemplate($this->getName(), $template));
        }
        
        return $result;
    }
    
    //FIXME
    public function getTraitInterfaceVariations(array $prefixes = null, array $suffixes = null): array {
        
        return [ $this->createNew($this->getName()) ];
    }
    
    public function toString(): string {
        
        return $this->getFullName();
    }
    
    public function __toString() {
        
        return $this->toString();
    }
}
