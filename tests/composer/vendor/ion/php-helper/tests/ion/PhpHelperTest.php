<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion;

/**
 * Description of PhpHelperTest
 *
 * @author Justus
 */

use PHPUnit\Framework\TestCase;
use \ion\PhpHelper as PHP;
use \ion\SystemType;

interface InterfaceA {
    
}

interface InterfaceB extends InterfaceA {
    
}

interface InterfaceC extends InterfaceB {
    
}

class ClassA implements InterfaceA {
    
}

class ClassB extends ClassA implements InterfaceB {
    
}

class ClassC extends ClassB implements InterfaceC {
    
}

class ClassD {
    private $private = 'private';
    protected $protected = 'protected';
    public $public = 'public';
	
    private function private() { }
    protected function protected() { }
    public function public() { }
	
}

class ClassE {
    public $int = 1;
    public $float = 1.111;
    public $string = 'STRING';
    public $bool = true;
}

class ClassF {
    public $int = 1;
    public $float = 1.111;
    public $string = 'STRING';
    public $bool = true;
    public $object = null;
    
    public function __construct() {
        
        $object = new ClassE();
    }
}

class CountableClass implements \Countable {
    
    public function count() {
        return 0;
    }
}

class PhpHelperTest extends TestCase {
    
    public function testIsAssociativeArray() {
        
        //isAssociativeArray(array $array)
        
        $assocArray = [
            'key_1' => 'value_1',
            'key_2' => 'value_2',
            'key_3' => 'value_3'
        ];
        
        $nonAssocArray = [
            'value_1',
            'value_2',
            'value_3'            
        ];
        
        $mixedArray = [
            'key_1' => 'value_1',
            'key_2' => 'value_2',
            'value_3',
            1,
            2,
            3
        ];
        
        $this->assertEquals(PHP::isAssociativeArray($assocArray), true);
        
        $this->assertEquals(PHP::isAssociativeArray($nonAssocArray), false);
        
        $this->assertEquals(PHP::isAssociativeArray($mixedArray), true);
        
        
    }
    
    public function testIsEmpty() {
        
        $emptyValues = [
            '',
            0,
            false,
            null
        ];
        
        foreach($emptyValues as $emptyValue) {
            $this->assertEquals(PHP::isEmpty($emptyValue, false), true);
        }
        
        $this->assertEquals(PHP::isEmpty('    ', false), false);
        
        $nonEmptyValues = [
            '  ',
            1,
            true
        ];        
        
        foreach($nonEmptyValues as $nonEmptyValue) {
            $this->assertEquals(PHP::isEmpty($nonEmptyValue, false), false);
        }        
    }
        
        
    
    /**
     * @depends testIsEmpty
     */    
    public function testIsEmptyOrWhiteSpaceIfString() {
        
        //isEmpty(/* mixed */ $value, bool $orWhiteSpaceIfString = true)
        
        $this->assertEquals(PHP::isEmpty('ABC', true), false);
        $this->assertEquals(PHP::isEmpty('', true), true);
        $this->assertEquals(PHP::isEmpty('    ', true), true);
        $this->assertEquals(PHP::isEmpty('    ', false), false);
        
    }    
    
    public function testInherits() {
     
//        $classA = new ClassA();
//        $classB = new ClassB();
//        $classC = new ClassC();
        
        $this->assertEquals(false, PHP::inherits(ClassA::class, ClassA::class));       
        //$this->assertEquals(true, PHP::inherits(ClassA::class, ClassA::class, true));
        
        $this->assertEquals(true, PHP::inherits(ClassB::class, ClassA::class));
        //$this->assertEquals(true, PHP::inherits(ClassB::class, ClassA::class, true));
        
        $this->assertEquals(true, PHP::inherits(ClassC::class, ClassA::class));
        //$this->assertEquals(true, PHP::inherits(ClassC::class, ClassA::class, true));
        
        $this->assertEquals(true, PHP::inherits(ClassC::class, ClassB::class));
        //$this->assertEquals(true, PHP::inherits(ClassC::class, ClassB::class, true));
        
    }
    
    public function testIsObject() {
      
        $int = 123;
        $string = 'string';
        $bool = true;
        $float = 1.234;
        
        $classA = new ClassA();
        $classB = new ClassB();
        $classC = new ClassC();
        
        $this->assertEquals(false, PHP::isObject($int));
        $this->assertEquals(false, PHP::isObject($string));
        $this->assertEquals(false, PHP::isObject($bool));
        $this->assertEquals(false, PHP::isObject($float));        
        
        $this->assertEquals(false, PHP::isObject($int, ClassA::class));
        $this->assertEquals(false, PHP::isObject($string, ClassA::class));
        $this->assertEquals(false, PHP::isObject($bool, ClassA::class));
        $this->assertEquals(false, PHP::isObject($float, ClassA::class));
        
        $this->assertEquals(true, PHP::isObject($classA));
        $this->assertEquals(true, PHP::isObject($classB));
        $this->assertEquals(true, PHP::isObject($classC));        
        
        $this->assertEquals(false, PHP::isObject($classA, ClassA::class, true, false));
        $this->assertEquals(true, PHP::isObject($classA, ClassA::class, true, true));        
        $this->assertEquals(false, PHP::isObject($classB, ClassB::class, true, false));
        $this->assertEquals(true, PHP::isObject($classB, ClassB::class, true, true));
        $this->assertEquals(false, PHP::isObject($classC, ClassC::class, true, false));
        $this->assertEquals(true, PHP::isObject($classC, ClassC::class, true, true));
        
        $this->assertEquals(true, PHP::isObject($classB, ClassA::class, true, false));        
        $this->assertEquals(true, PHP::isObject($classC, ClassA::class, true, false));
        $this->assertEquals(true, PHP::isObject($classC, ClassB::class, true, false));
        
        $this->assertEquals(true, PHP::isObject($classA, 'ion\\InterfaceA', true, true));

        
        $this->assertEquals(true, PHP::isObject($classC, ClassC::class, false));        
        $this->assertEquals(false, PHP::isObject($classC, ClassA::class, false));       
    }
    
    public function testIsArray() {
        $flatArray = [ 1, 2, 3 ];
        $assocArray = [ 'A' => 1, 'B' => 2, 'C' => 3];
        
        $this->assertEquals(true, PHP::isArray($flatArray, true));
        $this->assertEquals(true, PHP::isArray($flatArray, false));
        
        $this->assertEquals(true, PHP::isArray($assocArray, true));
        $this->assertEquals(false, PHP::isArray($assocArray, false));
    }
    
    public function testIsString() {
        
        $string = 'STRING';
        $int = 1;
        $float = 0.123;
        $bool = false;
        $array = [];
        $object = new ClassA();
        
        $this->assertEquals(true, PHP::isString($string));
        $this->assertEquals(false, PHP::isString($int));
        $this->assertEquals(false, PHP::isString($float));
        $this->assertEquals(false, PHP::isString($bool));
        $this->assertEquals(false, PHP::isString($array));
        $this->assertEquals(false, PHP::isString($object));
        
    }
    
    public function testIsFloat() {
        
        $string = 'STRING';
        $int = 1;
        $float = 0.123;
        $bool = false;
        $array = [];
        $object = new ClassA();
        
        $this->assertEquals(false, PHP::isFloat($string));
        $this->assertEquals(false, PHP::isFloat($int));
        $this->assertEquals(true, PHP::isFloat($float));
        $this->assertEquals(false, PHP::isFloat($bool));
        $this->assertEquals(false, PHP::isFloat($array));
        $this->assertEquals(false, PHP::isFloat($object));        
    }
    
    public function testIsInt() {
        
        $string = 'STRING';
        $int = 1;
        $float = 0.123;
        $bool = false;
        $array = [];
        $object = new ClassA();
        
        $this->assertEquals(false, PHP::isInt($string));
        $this->assertEquals(true, PHP::isInt($int));
        $this->assertEquals(false, PHP::isInt($float));
        $this->assertEquals(false, PHP::isInt($bool));
        $this->assertEquals(false, PHP::isInt($array));
        $this->assertEquals(false, PHP::isInt($object));        
    }
    
    public function testIsBool() {
        
        $string = 'STRING';
        $int = 1;
        $float = 0.123;
        $bool = false;
        $array = [];
        $object = new ClassA();
        
        $this->assertEquals(false, PHP::isBool($string));
        $this->assertEquals(false, PHP::isBool($int));
        $this->assertEquals(false, PHP::isBool($float));
        $this->assertEquals(true, PHP::isBool($bool));
        $this->assertEquals(false, PHP::isBool($array));
        $this->assertEquals(false, PHP::isBool($object));        
    }
    
    public function testIsType() {
        
        $string = 'STRING';
        $int = 1;
        $float = 0.123;
        $bool = false;
        $array = [];
        $object = new ClassA();
        
        // string
        
        $this->assertEquals(true, PHP::isType('string', $string));
        $this->assertEquals(false, PHP::isType('int', $string));
        $this->assertEquals(false, PHP::isType('float', $string));
        $this->assertEquals(false, PHP::isType('bool', $string));
        $this->assertEquals(false, PHP::isType('array', $string));
        $this->assertEquals(false, PHP::isType('object', $string));         
        $this->assertEquals(false, PHP::isType('ion\\ClassA', $string));
        
        // int
        
        $this->assertEquals(false, PHP::isType('string', $int));
        $this->assertEquals(true, PHP::isType('int', $int));
        $this->assertEquals(false, PHP::isType('float', $int));
        $this->assertEquals(false, PHP::isType('bool', $int));
        $this->assertEquals(false, PHP::isType('array', $int));
        $this->assertEquals(false, PHP::isType('object', $int));         
        $this->assertEquals(false, PHP::isType('ion\\ClassA', $int));

        // float
        
        $this->assertEquals(false, PHP::isType('string', $float));
        $this->assertEquals(false, PHP::isType('int', $float));
        $this->assertEquals(true, PHP::isType('float', $float));
        $this->assertEquals(false, PHP::isType('bool', $float));
        $this->assertEquals(false, PHP::isType('array', $float));
        $this->assertEquals(false, PHP::isType('object', $float));         
        $this->assertEquals(false, PHP::isType('ion\\ClassA', $float));

        // bool
        
        $this->assertEquals(false, PHP::isType('string', $bool));
        $this->assertEquals(false, PHP::isType('int', $bool));
        $this->assertEquals(false, PHP::isType('float', $bool));
        $this->assertEquals(true, PHP::isType('bool', $bool));
        $this->assertEquals(false, PHP::isType('array', $bool));
        $this->assertEquals(false, PHP::isType('object', $bool));         
        $this->assertEquals(false, PHP::isType('ion\\ClassA', $bool));

        // array
        
        $this->assertEquals(false, PHP::isType('string', $array));
        $this->assertEquals(false, PHP::isType('int', $array));
        $this->assertEquals(false, PHP::isType('float', $array));
        $this->assertEquals(false, PHP::isType('bool', $array));
        $this->assertEquals(true, PHP::isType('array', $array));
        $this->assertEquals(false, PHP::isType('object', $array));         
        $this->assertEquals(false, PHP::isType('ion\\ClassA', $array));

        // object / class
        
        $this->assertEquals(false, PHP::isType('string', $object));
        $this->assertEquals(false, PHP::isType('int', $object));
        $this->assertEquals(false, PHP::isType('float', $object));
        $this->assertEquals(false, PHP::isType('bool', $object));
        $this->assertEquals(false, PHP::isType('array', $object));
        $this->assertEquals(true, PHP::isType('object', $object));         
        $this->assertEquals(true, PHP::isType('ion\\ClassA', $object));
        $this->assertEquals(false, PHP::isType('non_existent_class', $object));
        
    }
    
    public function testGetObjectProperties() {
        $classD = new ClassD();
        
        $this->assertEquals(1, count(PHP::getObjectProperties($classD, true, false, false)));
        $this->assertEquals(1, count(PHP::getObjectProperties($classD, false, true, false)));
        $this->assertEquals(1, count(PHP::getObjectProperties($classD, false, false, true)));
        
        $this->assertEquals(1, count(PHP::getObjectProperties($classD, true, false, false)));
        $this->assertEquals(2, count(PHP::getObjectProperties($classD, true, true, false)));
        $this->assertEquals(3, count(PHP::getObjectProperties($classD, true, true, true)));      
        

        
    }
	
    public function testGetObjectPropertyValues() {
        $classD = new ClassD();
        
        $this->assertEquals(1, count(PHP::getObjectPropertyValues($classD, true, false, false)));
        $this->assertEquals(1, count(PHP::getObjectPropertyValues($classD, false, true, false)));
        $this->assertEquals(1, count(PHP::getObjectPropertyValues($classD, false, false, true)));
        
        $this->assertEquals(1, count(PHP::getObjectPropertyValues($classD, true, false, false)));
        $this->assertEquals(2, count(PHP::getObjectPropertyValues($classD, true, true, false)));
        $this->assertEquals(3, count(PHP::getObjectPropertyValues($classD, true, true, true)));      
        
        $values = PHP::getObjectPropertyValues($classD, true, true, true);

        $this->assertEquals('private', $values['private']);
        $this->assertEquals('protected', $values['protected']);
        $this->assertEquals('public', $values['public']);
        
    }	
    
    public function testGetObjectMethods() {
                
        $classD = new ClassD();
        
        $this->assertEquals(1, count(PHP::getObjectMethods($classD, true, false, false)));
        $this->assertEquals(1, count(PHP::getObjectMethods($classD, false, true, false)));
        $this->assertEquals(1, count(PHP::getObjectMethods($classD, false, false, true)));
        
        $this->assertEquals(1, count(PHP::getObjectMethods($classD, true, false, false)));
        $this->assertEquals(2, count(PHP::getObjectMethods($classD, true, true, false)));
        $this->assertEquals(3, count(PHP::getObjectMethods($classD, true, true, true)));      

    }	
    
    public function testGetArrayHash() {
        
        $array = [ 1, 1.1, true, false, new ClassF(), [ 3, 4, 5 ] ];
        
        $hash = PHP::getArrayHash($array);
        
        $this->assertEquals(173265430, $hash);        
    }
	
    public function testGetObjectHash() {
        
        $classF = new ClassF();
        
        $hash = PHP::getObjectHash($classF);
        
        $this->assertEquals(1093683956, $hash);
        
    }
    
    public function testGetServerRequestUri() {

        $this->assertEquals(null, PHP::getServerRequestUri());
    }
    
    public function testGetServerDocumentRoot() {

        $this->assertEquals(null, PHP::getServerDocumentRoot());
    }    
    
    public function testIsCommandLine() {
        $this->assertEquals(true, PHP::isCommandLine());
    }
    
    public function testIsWebServer() {
        $this->assertEquals(false, PHP::isWebServer());
    }
    
    public function testIsCountable() {
        
        $array = [1, 2, 3];
        $countableObject = new CountableClass();
        $nonCountableObject = new ClassA();
        $string = "ABC";
        $float = 0.1;
        
        
        $this->assertEquals(true, PHP::isCountable($array));
        $this->assertEquals(true, PHP::isCountable($countableObject));
        $this->assertEquals(false, PHP::isCountable($nonCountableObject));
        $this->assertEquals(false, PHP::isCountable($string));
        $this->assertEquals(false, PHP::isCountable($float));
        
    }
    
//    public function testGetCurrentSystem() {
//        $systemType = PHP::getOperatingSystemType();
//    }
}
