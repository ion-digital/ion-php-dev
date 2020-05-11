<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Regex;

/**
 * Description of FixtureTest
 *
 * @author Justus.Meyer
 */

use PHPUnit\Framework\TestCase;
use \Exception;

class RegexToolTest extends TestCase {
    
    public function testExecute() {
        
        $args = new \stdClass;
        
        $args->pattern = '';
        $args->replacement = '';
        $args->input = '';
        $args->output = '';
        $args->limit = -1;
        
        //TEMP
        $this->expectException(Exception::class);
        
        $tool = RegexTool::create($args, null, null);

        $this->assertEquals(0, $tool->execute());
        
    }    
    
}
