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
    
    private const PHP_CLASSES = [
      
        'Directory',
        'stdClass',
        '__PHP_Incomplete_Class',
        'Exception',
        'ErrorException',
        'php_user_filter',
        'Closure',
        'Generator',
        'ArithmeticError',
        'AssertionError',
        'DivisionByZeroError',
        'Error',
        'Throwable',
        'ParseError',
        'TypeError',
        'Traversable',
        'Iterator',
        'IteratorAggregate',
        'Throwable',
        'ArrayAccess',
        'Serializable',
        'WeakReference',
        'WeakMap',
        'Stringable',
        'DateTime',
        'DateInterval',
        'DateTimeImmutable'
    ];
    
    private const PHP_TYPES = [
      
        "string",
        "float",
        "int",
        "bool",
        "callable",
        "array",
        "resource"
    ];            
    
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
    private $absolute = false;
    
    public function __construct(array $namespace = null, string $name = null, bool $absolute = false) {
        
        $this->namespace = $namespace;
        $this->absolute = $absolute;
        $this->setName($name);
    }
    
    public function setName(string $name = null): self {
        
        $this->name = $name;
        return $this;
    }
    
    public function getName(): ?string {
        
        return $this->name;
    }
    
    public function hasName(): bool {
     
        return !empty($this->getName());
    }
    
    public function getFullName(): string {

        if(!$this->isStruct()) {
            
            return $this->getName();
        }
        
        return $this->getNamespace() . ($this->hasName() ? ($this->hasNamespace() || $this->isAbsolute() ? "\\" : "") . "{$this->getName()}" : "");
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
        
        return ($this->isAbsolute() ? "\\" : "") . implode("\\", $this->namespace);
    }
    
    public function hasNamespace(): bool {
        
        return (!empty($this->getNameSpace(true)));
    }
    
    public function createNew(string $structName, array $namespace = null, bool $absolute = false): self {
        
        $obj = clone $this;
        
        $obj->setName($structName);
        $obj->setAbsolute($absolute);
        $obj->setNamespaceParts($namespace);
        
        return $obj;
    }
    
    public function isPhpType(): bool {
        
        return (in_array($this->getName(), self::PHP_TYPES));
    }
    
//    public function isPhpStruct(): bool {
//        
//        return ();
//    }
    
    public function isPhpClass(): bool {
        
        return (in_array($this->getName(), self::PHP_CLASSES) && $this->isStruct() && !$this->hasNamespace());
    }    
    
    public function isStruct(): bool {
        
        if($this->isPhpType()) {
            
            return false;
        }
        
        return true;
//        return ($this->hasNamespace() || $this->isAbsolute());
    }
    
    public function asInterfaceName(
            
            string $template,
            array $prefixesToStrip = [],
            array $suffixesToStrip = [],
            array $prefixesToIgnore = [],
            array $suffixesToIgnore = []
            
        ): self {

        return static::createNew(static::applyTemplate($this->getModifiedName(
                
                $prefixesToStrip,
                $suffixesToStrip,
                $prefixesToIgnore,
                $suffixesToIgnore
                
            )->getName(), $template), $this->getNamespaceParts());
    }
    
    private static function applyTemplate(string $structName, string $template): string {
        
        return str_replace("*", $structName, $template);
    }
    
    public function getInterfaceVariations(array $templates, array $prefixesToStrip = [], array $suffixesToStrip = [], array $prefixesToIgnore = [], array $suffixesToIgnore = []): array {
        
        $result = [];
        
        foreach($templates as $template) {
            
            $result[] = static::createNew(static::applyTemplate($this->getModifiedName(
                    
                    $prefixesToStrip,
                    $suffixesToStrip,
                    $prefixesToIgnore,
                    $suffixesToIgnore
                    
                )->getName(), $template), $this->getNamespaceParts());
        }        

        return $result;
    }
    
    public function getModifiedName(
            
            array $prefixesToStrip = [],
            array $suffixesToStrip = [],
            array $prefixesToIgnore = [],
            array $suffixesToIgnore = []
            
        ): self {
                
        $tmp = $this->getName();        
        
        foreach($prefixesToStrip as $prefixToStrip) {

            foreach($prefixesToIgnore as $prefixToIgnore) {

                if(preg_match("/^({$prefixToIgnore})/", $tmp)) {

                    continue 2;
                }
            }

            $pattern = "/^({$prefixToStrip})/";

            if(!preg_match($pattern, $tmp)) {

                continue;
            }

            $tmp = preg_replace($pattern, '', $tmp, 1);  
            break;
        }

        foreach($suffixesToStrip as $suffixToStrip) {
            
            foreach($suffixesToIgnore as $suffixToIgnore) {

                if(preg_match("/^({$suffixToIgnore})/", $tmp)) {

                    continue 2;
                }
            }            
            
            $pattern = "/({$suffixToStrip})\$/";
            
            if(!preg_match($pattern, $tmp)) {

                continue;
            }

            $tmp = preg_replace($pattern, '', $tmp, 1);
            break;
        }           
        
        
        return $this->createNew($tmp);
    }

    public function toString(): string {
        
        return $this->getFullName();
    }
    
    public function __toString() {
        
        return $this->toString();
    }
}
