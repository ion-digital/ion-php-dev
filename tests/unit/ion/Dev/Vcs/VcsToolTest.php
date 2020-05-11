<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Vcs;

/**
 * Description of FixtureTest
 *
 * @author Justus.Meyer
 */

use PHPUnit\Framework\TestCase;

class VcsToolTest extends TestCase {
    
    public function testExecute() {
        
        $args = new \stdClass;
        
        $args->operation = '';
        $args->source = '';
        
        //$transformer = VcsTool::create($args, null, null);

        //$this->assertEquals(0, $transformer->execute());
        
    }    
    
}
