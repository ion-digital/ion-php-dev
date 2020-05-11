<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

/**
 * Description of PhpHelper
 *
 * @author Justus
 */
use ion\Types\IStringObject;
use ion\Types\IEnum;
use ReflectionObject;
use ReflectionProperty;
use ReflectionMethod;
use ion\Types\Arrays\IMap;
use ion\Types\Arrays\Map;

class PhpHelper implements IPhpHelper
{
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isAssociativeArray(array $array) : bool
    {
        if (!is_array($array)) {
            return false;
        }
        if ([] === $array) {
            return false;
        }
        return (bool) (array_keys($array) !== range(0, count($array) - 1));
        //return (bool) (array_keys($array) !== array_values($array));
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isEmpty($value, bool $orWhiteSpaceIfString = true) : bool
    {
        if (is_array($value) && count($value) === 0) {
            return true;
        }
        if (is_string($value) && $orWhiteSpaceIfString === true) {
            return (bool) empty(trim($value));
        }
        return (bool) empty($value);
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function inherits(string $childClassName, string $parentClassName) : bool
    {
        return is_subclass_of($childClassName, $parentClassName, true);
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isObject($variable, string $className = null, bool $parent = true, bool $class = true) : bool
    {
        if (!is_object($variable)) {
            return false;
        }
        if ($className !== null && $parent === true) {
            if ($class === true) {
                return is_a($variable, $className, true) || static::inherits(get_class($variable), $className);
            }
            return static::inherits(get_class($variable), $className);
        }
        if ($className !== null && $parent === false) {
            return get_class($variable) === $className;
        }
        return true;
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isArray($variable, bool $isAssociative = true) : bool
    {
        if (!is_array($variable)) {
            return false;
        }
        if (static::isAssociativeArray($variable) && !$isAssociative) {
            return false;
        }
        return true;
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isString($variable = null) : bool
    {
        if ($variable === null) {
            return false;
        }
        return is_string($variable);
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isFloat($variable = null) : bool
    {
        if ($variable === null) {
            return false;
        }
        return is_float($variable);
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isInt($variable = null) : bool
    {
        if ($variable === null) {
            return false;
        }
        return is_int($variable);
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isBool($variable = null) : bool
    {
        if ($variable === null) {
            return false;
        }
        return is_bool($variable);
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isType(string $typeString, $variable = null) : bool
    {
        switch (strtolower(trim($typeString))) {
            case 'null':
                return $variable === null;
            case 'array':
                return static::isArray($variable);
            case 'int':
                return static::isInt($variable);
            case 'float':
                return static::isFloat($variable);
            case 'string':
                return static::isString($variable);
            case 'bool':
                return static::isBool($variable);
            case 'object':
                return static::isObject($variable);
        }
        return static::isObject($variable, $typeString, true, true);
    }
    
    /**
     * method
     * 
     * 
     * @return array
     */
    
    public static function getObjectProperties($object, bool $public = true, bool $protected = false, bool $private = false) : array
    {
        $reflector = new ReflectionObject($object);
        $propertyFilter = ($public ? ReflectionProperty::IS_PUBLIC : 0) | ($protected ? ReflectionProperty::IS_PROTECTED : 0) | ($private ? ReflectionProperty::IS_PRIVATE : 0);
        $result = [];
        foreach ($reflector->getProperties($propertyFilter) as $property) {
            $result[$property->getName()] = $property;
        }
        return $result;
    }
    
    /**
     * method
     * 
     * 
     * @return array
     */
    
    public static function getObjectPropertyValues($object, bool $public = true, bool $protected = false, bool $private = false) : array
    {
        $properties = static::getObjectProperties($object, $public, $protected, $private);
        $result = [];
        foreach ($properties as $name => $property) {
            if ($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
            }
            $result[$name] = $property->getValue($object);
        }
        return $result;
    }
    
    /**
     * method
     * 
     * 
     * @return array
     */
    
    public static function getObjectMethods($object, bool $public = true, bool $protected = false, bool $private = false, bool $abstract = true, bool $final = true) : array
    {
        $reflector = new ReflectionObject($object);
        $methodFilter = ($public ? ReflectionMethod::IS_PUBLIC : 0) | ($protected ? ReflectionMethod::IS_PROTECTED : 0) | ($private ? ReflectionMethod::IS_PRIVATE : 0) | ($abstract ? ReflectionMethod::IS_ABSTRACT : 0) | ($final ? ReflectionMethod::IS_FINAL : 0);
        $result = [];
        foreach ($reflector->getMethods($methodFilter) as $method) {
            $result[$method->getName()] = $method;
        }
        return $result;
    }
    
    /**
     * method
     * 
     * 
     * @return int
     */
    
    public static function getArrayHash(array $array) : int
    {
        $sig = [(string) count($array)];
        if (static::isAssociativeArray($array)) {
            $sig[] = (string) static::getArrayHash(array_keys($array));
        }
        foreach (array_values($array) as $value) {
            if (static::isObject($value)) {
                $sig[] = (string) static::getObjectHash($value);
                continue;
            }
            if (static::isArray($value)) {
                $sig[] = (string) static::getArrayHash($value);
                continue;
            }
            $sig[] = (string) $value;
        }
        return crc32(join('', $sig));
    }
    
    /**
     * method
     * 
     * 
     * @return int
     */
    
    public static function getObjectHash($object) : int
    {
        $sig = [get_class($object)];
        $properties = static::getObjectPropertyValues($object, true, true, true);
        $sig[] = static::getArrayHash($properties);
        //        foreach($properties as $property => $value) {
        //
        //            if(static::isObject($value)) {
        //
        //                $sig[] = (string) static::getObjectHash($value);
        //
        //                continue;
        //            }
        //
        //            if(static::isArray($value)) {
        //
        //                $sig[] = (string) static::getArrayHash($value);
        //
        //                continue;
        //            }
        //
        //            $sig[] = (string) $value;
        //
        //        }
        return crc32(join('', $sig));
    }
    
    /**
     * method
     * 
     * @return ?string
     */
    
    public static function getServerRequestUri() : ?string
    {
        if (!static::isWebServer()) {
            return null;
        }
        $uri = (string) filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
        if (static::isEmpty($uri, true)) {
            $uri = (string) $_SERVER['REQUEST_URI'];
        }
        if (static::isEmpty($uri, true)) {
            return null;
        }
        return (string) $uri;
    }
    
    /**
     * method
     * 
     * @return ?string
     */
    
    public static function getServerDocumentRoot() : ?string
    {
        if (!static::isWebServer()) {
            return null;
        }
        $uri = (string) filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_DEFAULT);
        if (static::isEmpty($uri, true)) {
            $uri = (string) $_SERVER['DOCUMENT_ROOT'];
        }
        if (static::isEmpty($uri, true)) {
            return null;
        }
        return (string) $uri . DIRECTORY_SEPARATOR;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    public static function isCommandLine() : bool
    {
        if (php_sapi_name() === 'cli') {
            return true;
        }
        return false;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    public static function isWebServer() : bool
    {
        return !static::isCommandLine();
    }
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    public static function isCountable($variable) : bool
    {
        if (PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION >= 3) {
            return (bool) is_countable($variable);
        }
        if (static::isObject($variable)) {
            return static::inherits(get_class($variable), \Countable::class);
        }
        return static::isArray($variable);
    }

}