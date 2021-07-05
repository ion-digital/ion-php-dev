<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation;

/**
 * Description of DocumentationGenerators
 *
 * @author Justus
 */

use \ion\Dev\Documentation\Generators\PhpDocDocumentationGenerator;

abstract class DocumentationGenerators {
   
    public static function get() {
        
        return [
        
            PhpDocDocumentationGenerator::getClassKey() => PhpDocDocumentationGenerator::class
                
        ];
    }
}
