<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Templates;

/**
 * Description of FixtureTest
 *
 * @author Justus.Meyer
 */

use PHPUnit\Framework\TestCase;
use ion\Dev\Templates\Fixture;
//use \ion\Package;

class FixtureTest extends TestCase {
    
    const INPUT_FILE = 'tests' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'input' . DIRECTORY_SEPARATOR . 'load.xml';
    const OUTPUT_DIR = 'tests' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR;
    const OUTPUT_FILE = self::OUTPUT_DIR . 'inherited.txt';
    
    public function testDirs() {
        
        $pkgRoot = getcwd() . DIRECTORY_SEPARATOR;
        
        $this->assertTrue(file_exists( $pkgRoot . static::INPUT_FILE));
        $this->assertTrue(is_dir( $pkgRoot . static::OUTPUT_DIR));
    }
    
    public function testLoad() {
        
        //$pkgRoot = Package::getInstance('ion/core-dev')->getProjectRoot();
        $pkgRoot = getcwd() . DIRECTORY_SEPARATOR;
        
        $fixtures = [];
        
        $fixtures = Fixture::load($pkgRoot . static::INPUT_FILE, $fixtures, function($path) {

            /* empty! */
        });

        $this->assertArrayHasKey('base', $fixtures);
        $this->assertArrayHasKey('parent', $fixtures);
        $this->assertArrayHasKey('child', $fixtures);

        // base
        
        $base = $fixtures['base'];        
        
        $this->assertArrayHasKey('a', $base->getTags());
        $this->assertArrayHasKey('b', $base->getTags());
        $this->assertArrayHasKey('c', $base->getTags());
        
        //print_r($base->getTags());
        
        $this->assertContains('A1', $base->getTags()['a']['content']);
        $this->assertContains('B1', $base->getTags()['b']['content']);
        $this->assertContains('C1', $base->getTags()['c']['content']);

        $this->assertEquals('base', $base->getName());
        
        $this->assertEquals(0, count($base->getFixtures()));
        
        //$this->assertEquals($pkgRoot . static::OUTPUT_DIR, $base->getBase());
        
        $this->assertNull($base->getParent());
        $this->assertNull($base->getOutput());   
        $this->assertEquals(1, count($base->getTemplates()));
        
        $this->assertEquals('replace', $base->getMethod());
        
        // inherited
        
        $inherited = $fixtures['child'];        

        $this->assertArrayHasKey('a', $inherited->getTags());
        $this->assertArrayHasKey('b', $inherited->getTags());
        $this->assertArrayHasKey('d', $inherited->getTags());        
        
        $this->assertContains('A2', $inherited->getTags()['a']['content']);
        $this->assertContains('B2', $inherited->getTags()['b']['content']);
        $this->assertContains('D2', $inherited->getTags()['d']['content']);        
        
        $this->assertEquals('child', $inherited->getName());
        
        $this->assertEquals(2, count($inherited->getFixtures()));    
        
        
        //$this->assertEquals($pkgRoot . static::OUTPUT_DIR, $inherited->getBase());
        
        $this->assertNotNull($inherited->getParent());
        $this->assertNotNull($inherited->getOutput());        
        $this->assertEquals(1, count($inherited->getTemplates()));  
        
        $this->assertEquals('append', $inherited->getMethod());        

        return $fixtures;
    }    

    
    
    public function testApply() {
 
        
        
        $createParent = function(array &$fixtures, string $tagMethod, string $templateMethod) {
            
            return Fixture::create('parent', [

                'a' => [ 'content' => [ 'A', '1' ], 'method' => $tagMethod , 'filters' => [ 'trim' ] ],
                'b' => [ 'content' => [ 'B', '2' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ],
                'c' => [ 'content' => [ 'C', '3' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ],
                'p' => [ 'content' => [ 'P', '0' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ]

            ], $fixtures, $templateMethod, null, null, null, [ 
                [ 'content' => [ '/* {a} */' ], 'filters' => [ 'trim' ] ], 
                [ 'content' => [ '/* {b} */' ], 'filters' => [ 'trim' ] ], 
                [ 'content' => [ '/* {c} */' ], 'filters' => [ 'trim' ] ]
                ]);            
        };

        $createChild = function(array &$fixtures, string $tagMethod, string $templateMethod) {
            
            return Fixture::create('child', [

                'a' => [ 'content' => [ 'X', '7' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ],
                'b' => [ 'content' => [ 'Y', '8' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ],
                'c' => [ 'content' => [ 'Z', '9' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ],
                'm' => [ 'content' => [ 'M', '0' ], 'method' => $tagMethod, 'filters' => [ 'trim' ] ]

            ], $fixtures, $templateMethod, 'parent', null, null, [ 
                [ 'content' => [ 'x' ], 'filters' => [ 'trim' ] ], 
                [ 'content' => [ 'y' ], 'filters' => [ 'trim' ] ],
                [ 'content' => [ 'z' ], 'filters' => [ 'trim' ] ]
                ] );            
        };

        
        // Test tags: 'parent'; template: 'append'
        
        $fixtures = [];
        $this->assertEquals(0, count($fixtures));
        
        $parent = $createParent($fixtures, 'parent', 'append');
        $child = $createChild($fixtures, 'parent', 'append');
        
        $this->assertEquals(2, count($fixtures));
        
        $this->assertEquals('A1B2C3', $parent->render(false));
        $this->assertEquals('xyz', $child->render(false));
        $this->assertEquals('A1B2C3xyz', $child->render(true));
        
        
        // Test tags: 'child'; template: 'append'
        $fixtures = [];
        $this->assertEquals(0, count($fixtures));
        
        $parent = $createParent($fixtures, 'child', 'append');
        $child = $createChild($fixtures, 'child', 'append');        
        
        $this->assertEquals(2, count($fixtures));
        
        $this->assertEquals('A1B2C3', $parent->render(false));
        $this->assertEquals('xyz', $child->render(false));
        $this->assertEquals('X7Y8Z9xyz', $child->render(true));
        
        
        // Test tags: 'append'; template: 'append'
        $fixtures = [];
        $this->assertEquals(0, count($fixtures));
        
        $parent = $createParent($fixtures, 'append', 'append');
        $child = $createChild($fixtures, 'append', 'append');        
        
        $this->assertEquals(2, count($fixtures));

        $this->assertEquals('A1B2C3', $parent->render(false));
        $this->assertEquals('xyz', $child->render(false));
        $this->assertEquals('A1X7B2Y8C3Z9xyz', $child->render(true));

        
        // Test tags: 'prepend'; template: 'append'
        $fixtures = [];
        $this->assertEquals(0, count($fixtures));
        
        $parent = $createParent($fixtures, 'prepend', 'append');
        $child = $createChild($fixtures, 'prepend', 'append');        
        
        $this->assertEquals(2, count($fixtures));
        
        $this->assertEquals('A1B2C3', $parent->render(false));
        $this->assertEquals('xyz', $child->render(false));
        $this->assertEquals('X7A1Y8B2Z9C3xyz', $child->render(true));
        

    }
    
//    /**
//     * @depends testApply
//     */
//    
//    public function testRender(Fixture $applied) {
//        
//        //var_dump($fixtures);        
//        //$this->assertContains('trim', $inherited->getFilters());  
//        
//        $this->assertEquals('BASEPARENTCHILD (INTERNAL)A2B2C1D2CHILD (EXTERNAL)A2B2C1D2', $applied->render());
//        
//        
//    }    
    
    /**
     * @depends testLoad
     */    
    
    public function testGenerate(array $fixtures) {
        
        $inherited = $fixtures['child'];
        
        
        
        $pkgRoot = getcwd() . DIRECTORY_SEPARATOR;
        $f = $pkgRoot . self::OUTPUT_FILE;
        
        $this->assertEquals(true, is_dir($pkgRoot));
        
        $inherited->generate();
        
        //$this->assertEquals(true, file_exists($f));
        
    }
    
    public function testFilters() {
        
        $this->assertEquals("X", Fixture::applyFilters("\n X \n", [ 'trim' ]));
        $this->assertEquals("X \n", Fixture::applyFilters("\n X \n", [ 'trim-left' ]));
        $this->assertEquals("\n X", Fixture::applyFilters("\n X \n", [ 'trim-right' ]));
        $this->assertEquals(" X ", Fixture::applyFilters("\n\r\n\r X \n\r\n\r", [ 'trim-paragraph' ]));        
        $this->assertEquals("X", Fixture::applyFilters("<?php echo 'X';", [ 'eval' ]));
    }
    
    public function testTags() {
        
        $this->assertEquals('z', Fixture::applyTags("[x]", [ 'x' => [ 'filters' => [ '' ], 'content' => [ 'z' ] ]], [], '\[', '\]'));
        
        $this->assertEquals('z', Fixture::applyTags(" [x] ", [ 'x' => [ 'filters' => [ 'clear' ], 'content' => [ 'z' ] ]], [], '\[', '\]'));
        $this->assertEquals('z ', Fixture::applyTags(" [x] ", [ 'x' => [ 'filters' => [ 'clear-left' ], 'content' => [ 'z' ] ]], [], '\[', '\]'));
        $this->assertEquals(' z', Fixture::applyTags(" [x] ", [ 'x' => [ 'filters' => [ 'clear-right' ], 'content' => [ 'z' ] ]], [], '\[', '\]'));
        
    }
    
    public function testDefaults() {
        
        $fixtures = [];
        
        $parent = Fixture::create('parent', [

            'a' => [ 'content' => [ 'A' ], 'filters' => [], 'method' => null ]

        ], $fixtures, 'append', null, null, null, [ 
            [ 'content' => [ '/* {a} *//* {b} */' ], 'filters' => [], 'method' => null ]
        ], 
        [
            'b' => [ 'content' => [ 'B' ], 'filters' => [], 'method' => null ]
        ]);
        
        $this->assertEquals(1, count($parent->getTags()));
        $this->assertEquals(1, count($parent->getDefaults()));
        
        $this->assertEquals('AB', $parent->render(true));
        
        
        $child = Fixture::create('child', [

            'c' => [ 'content' => [ 'C' ], 'filters' => [], 'method' => null ]

        ], $fixtures, 'append', 'parent', null, null, [ 
            [ 'content' => [ '/* {c} *//* {d} */' ], 'filters' => [], 'method' => null ]
        ], 
        [
            'd' => [ 'content' => [ 'D' ], 'filters' => [], 'method' => null ]
        ]);           
        
        $this->assertEquals(1, count($child->getTags()));
        $this->assertEquals(1, count($child->getDefaults()));        
        
        $this->assertEquals('ABCD', $child->render(true));
        
    }
    
}
