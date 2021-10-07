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

use \Exception;

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
        'Throwable',        
        'Serializable',
        'WeakReference',
        'WeakMap',
        'Stringable',
        'DateTime',
        'DateInterval',
        'DateTimeImmutable'
    ];
    
    private const PHP_INTERFACES = [
      
        'ArrayAccess',
        'Countable',
        'Traversable',
        'Iterator',
        'IteratorAggregate'        
    ];
    
    private const PHP_TYPES = [
      
        "string",
        "float",
        "int",
        "bool",
        "callable",
        "array",
        "resource",
        "void",
        "object",
        "self",
        "static"
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
    
    public function copy(): self {
        
        return $this->createNew($this->getName(), $this->getNamespaceParts(), $this->getAbsolute());
    }
    
    public function isPhpType(): bool {
        
        return (in_array($this->getName(), self::PHP_TYPES));
    }
    
    public function isPhpStruct(): bool {
        
        return ($this->isPhpClass() || $this->isPhpInterface());
    }
    
    public function isPhpClass(): bool {
        
        return (in_array($this->getName(), self::PHP_CLASSES) && $this->isStruct() && !$this->hasNamespace());
    }    
    
    public function isPhpInterface(): bool {
        
        return (in_array($this->getName(), self::PHP_INTERFACES) && $this->isStruct() && !$this->hasNamespace());
    }        
    
    public function isStruct(): bool {
        
        if($this->isPhpType()) {
            
            return false;
        }
        
        return true;
//        return ($this->hasNamespace() || $this->isAbsolute());
    }
    
//    public function asInterfaceName(
//            
//            string $template,
//            array $prefixesToStrip = [],
//            array $suffixesToStrip = [],
//            array $prefixesToIgnore = [],
//            array $suffixesToIgnore = []
//            
//        ): self {
//
//        return static::createNew(static::applyTemplate($this->getModifiedName(
//                
//                $prefixesToStrip,
//                $suffixesToStrip,
//                $prefixesToIgnore,
//                $suffixesToIgnore
//                
//            )->getName(), $template), $this->getNamespaceParts());
//    }
    
    public function asInterfaceName(string $template): NameModel {
        
        $name = $this->copy();

        $prefixesToStrip = [];
        $suffixesToStrip = [];
        $prefixesToIgnore = [];
        $suffixesToIgnore = [];

        $tmp = substr($template, 0, strpos($template, "*"));

        if(!empty($tmp)) {

            $prefixesToStrip[] = "{$tmp}";
            $prefixesToIgnore[] = "{$tmp}[A-Z]";

        }

        $tmp = substr($template, strpos($template, "*") + 1);

        if(!empty($tmp)) {

            $suffixesToStrip[] = "{$tmp}";
            //$suffixesToIgnore[] = "";
        }

//            $name = static::createNew(static::applyTemplate($name->getModifiedName(
//                    
//                $prefixesToStrip,
//                $suffixesToStrip,
//                $prefixesToIgnore,
//                $suffixesToIgnore
//                    
//            )->getName(), $template), $name->getNamespaceParts());  

        $name = static::createNew(static::applyTemplate($name->getName(), $template), $name->getNamespaceParts());  


        return $name;     
    }    
    
    private static function applyTemplate(string $structName, string $template): string {
        
        return str_replace("*", $structName, $template);
    }
    
    public function getInterfaceVariations(
            
            array $templates,
            array $prefixesToStrip = [],
            array $suffixesToStrip = [],
            array $prefixesToIgnore = [],
            array $suffixesToIgnore = []
            
        ): array {
        
        $result = [];
        
//echo "\n\n=========================\n\n";        
        
        $tmp = $this->getModifiedName($prefixesToStrip, $suffixesToStrip, $prefixesToIgnore, $suffixesToIgnore);
        
        if(empty($tmp)) {
            
            return [];
        }
        
//echo "\n\n---\n\n";  

//var_dump($prefixesToStrip);
//var_dump($suffixesToStrip);
//var_dump($prefixesToIgnore);
//var_dump($suffixesToIgnore);
//var_dump($tmp);   

//echo "\n\n=========================\n\n";         
        
        foreach($templates as $template) {
            
//            echo "\n\n=========================\n\n";
//            //var_dump($this);
//            var_dump($this->getInterfaceVariations(
//                
//                $templates,
//                $prefixesToStrip,
//                $suffixesToStrip,
//                $prefixesToIgnore,
//                $suffixesToIgnore
//                
//            ));
//            echo "\n\n=========================\n\n";             
            
            $result[] = $tmp->asInterfaceName($template);
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

        if(empty($tmp)) {
            
            throw new Exception("Name is empty?");
        }
        
        foreach($prefixesToStrip as $prefixToStrip) {

            if(count($prefixesToIgnore) > 0) {
            
                foreach($prefixesToIgnore as $prefixToIgnore) {

                    if(empty($prefixToIgnore)) {
                        
                        continue;
                    }                    
                    
                    if(preg_match("/^({$prefixToIgnore})/", $tmp)) {

                        continue 2;
                    }
                }
            }

            $pattern = "/^({$prefixToStrip})/";

            if(!preg_match($pattern, $tmp)) {

                continue;
            }

            $tmp = preg_replace($pattern, '', $tmp, 1);  
            break;
        }

//echo "\n\n=========================\n\n";
////var_dump($suffixesToStrip);
//var_dump($suffixesToIgnore);
//echo "\n\n=========================\n\n";        
        
        foreach($suffixesToStrip as $suffixToStrip) {
//echo "A";            
            if(count($suffixesToIgnore) > 0) {
                
                foreach($suffixesToIgnore as $suffixToIgnore) {

                    if(empty($suffixToIgnore)) {
                        
                        continue;
                    }
                    
                    if(preg_match("/({$suffixToIgnore})\$/", $tmp)) {

                        continue 2;
                    }
                }            
            }
//echo "B";

            $pattern = "/({$suffixToStrip})\$/";
            
//            var_dump($pattern);
            
            if(!preg_match($pattern, $tmp)) {

                continue;
            }

            $tmp = preg_replace($pattern, '', $tmp, 1);
            break;
        }           
        
//var_dump($tmp);        
        
        return $this->createNew($tmp, $this->getNamespaceParts());
    }

    public function toString(): string {
        
        return $this->getFullName();
    }
    
    public function __toString() {
        
        return $this->toString();
    }
}
