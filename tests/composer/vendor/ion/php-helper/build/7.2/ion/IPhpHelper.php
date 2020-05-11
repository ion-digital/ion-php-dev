<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

use ion\Types\Arrays\IMap;

interface IPhpHelper
{
    /**
     * Checks to see if an array is an associative array or not.
     *
     * @since 0.0.1
     * 
     * @param array $array The array to check.
     * @return bool Returns __true__ if the array is an associative array, __false__ if not.
     *
     */
    
    static function isAssociativeArray(array $array) : bool;
    
    /**
     * Checks to see if a value is empty or not - additionally includes special handling for strings.
     *
     * @since 0.0.2
     * 
     * @param mixed $variable The variable to check.
     * @param bool $orWhiteSpaceIfString Return __true__ if _$value_ is a string and is empty or consists out of white-space.
     * @return bool Returns __true__ if the variable is empty, __false__ if not.
     *
     */
    
    static function isEmpty($variable, bool $orWhiteSpaceIfString = true) : bool;
    
    /**
     * Checks to see if a class inherits another class.
     *
     * @since 0.0.9
     * 
     * @param string $childClassName The name of the class to be checked.
     * @param string $parentClassName The name of the class to validate as a parent.
     * @return bool Returns __true__ if the child class inherits the parent class, __false__ if not.
     *
     */
    
    static function inherits(string $childClassName, string $parentClassName) : bool;
    
    /**
     * Checks to see if a variable is an object, or an object of a certain type.
     *
     * @since 0.0.9
     * 
     * @param mixed $variable The variable that needs to be checked.
     * @param string $className The name of the class to validate.
     * @param bool $parent If set to __true__, will validate if class to validate is a parent class - otherwise it will check if $variable is of type $className.
     * @param bool $class If set to __true__ and $parent is set to __true__, the specified class will be included in the check.
     * @return bool Returns __true__ if the variable is an object and if child class inherits the parent class (if $parentClassName is specified), __false__ if not.
     *
     */
    
    static function isObject($variable, string $className = null, bool $parent = true, bool $class = true) : bool;
    
    /**
     * Checks to see if a variable is an array - and additionally will filter for either associative or flat arrays, both or neither.
     *
     * @since 0.3.4
     * 
     * @param mixed $variable The variable to check.
     * @param bool $isAssociative Include associative arrays in the result.
     * @return bool Returns __true__ if the array is an array that matches the parameters, __false__ if not.
     *
     */
    
    static function isArray($variable, bool $isAssociative = true) : bool;
    
    static function isString($variable = null) : bool;
    
    static function isFloat($variable = null) : bool;
    
    static function isInt($variable = null) : bool;
    
    static function isBool($variable = null) : bool;
    
    static function isType(string $typeString, $variable = null) : bool;
    
    /**
     * 
     * Return the non-static properties of an instantiated object.
     * 
     * @since 0.2.2
     * 
     * @param object $object The object for which to return the properties of.
     * @param bool $public Return public properties.
     * @param bool $protected Return protected properties.
     * @param bool $private Return private properties.
     * 
     * @return array Return the properties and their values as an associative array.
     */
    
    static function getObjectProperties(object $object, bool $public = true, bool $protected = false, bool $private = false) : array;
    
    /**
     * 
     * Return the non-static property names and values of an instantiated object.
     * 
     * @param object $object The object for which to return the properties of.
     * @param bool $public Return public properties.
     * @param bool $protected Return protected properties.
     * @param bool $private Return private properties.
     * 
     * @return array Return the properties and their values as an  associative array.
     */
    
    static function getObjectPropertyValues(object $object, bool $public = true, bool $protected = false, bool $private = false) : array;
    
    /**
     * 
     * Return the non-static methods of an instantiated object.
     * 
     * @since 0.2.2
     * 
     * @param object $object The object for which to return the methods of.
     * @param bool $public Return public methods.
     * @param bool $protected Return protected methods.
     * @param bool $private Return private methods.
     * @param bool $abstract Return private methods.
     * @param bool $final Return private methods.
     * 
     * @return array Return the methods and their callables as an  associative array.
     */
    
    static function getObjectMethods(object $object, bool $public = true, bool $protected = false, bool $private = false, bool $abstract = true, bool $final = true) : array;
    
    /**
     * 
     * Return a unique hash that represents the properties of this object.
     * 
     * @param array $array The array for which to return the hash for.
     * 
     * @return int Return the hash as an int.
     */
    
    static function getArrayHash(array $array) : int;
    
    /**
     * 
     * Return a unique hash that represents the properties of this object.
     * 
     * @param object $object The object for which to return the hash for.
     * 
     * @return int Return the hash as an int.
     */
    
    static function getObjectHash(object $object) : int;
    
    /**
     * Returns the value of the $_SERVER['REQUEST_URI'] variable.
     * 
     * @return ?string Return the value.
     */
    
    static function getServerRequestUri() : ?string;
    
    /**
     * Returns the value of the $_SERVER['DOCUMENT_ROOT'] variable.
     * 
     * @return ?string Return the value.
     */
    
    static function getServerDocumentRoot() : ?string;
    
    /**
     * Return whether the current script is running in a command-line context, or somewhere else (like a web server).
     * 
     * @return bool Returns __true__ if we are running as a command-line script - __false__ otherwise. 
     */
    
    static function isCommandLine() : bool;
    
    /**
     * Return whether the current script is running in a Web context, or somewhere else (like the command-line).
     * 
     * @return bool Returns __true__ if we are running as a Web script - __false__ otherwise. 
     */
    
    static function isWebServer() : bool;
    
    /**
     * Return whether a variable is countable.
     * 
     * @return bool Returns __true__ if it is - __false__ otherwise. 
     */
    
    static function isCountable($variable) : bool;

}