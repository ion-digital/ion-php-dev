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
use ion\Composer\Core\FileOutput;

class TransformToolTest extends TestCase {
    
    private const ROOT = 'tests' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    
    public function testExecute() {
        
        $args = new \stdClass;
        
        $args->operation = 'generate';
        $args->fixtures = self::ROOT . 'input' . DIRECTORY_SEPARATOR . 'load.xml';
        
        $transformer = TransformTool::create($args, null /*, new FileOutput(self::ROOT . 'output' . DIRECTORY_SEPARATOR . 'output.log') */);

        $this->assertEquals(0, $transformer->execute());
        
    }

}
