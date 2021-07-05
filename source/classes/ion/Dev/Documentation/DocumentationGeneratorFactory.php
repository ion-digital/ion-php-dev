<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation;

/**
 * Description of Generator
 *
 * @author Justus
 */

use \ion\Dev\Documentation\Generators\PhpDocDocumentationGenerator;
use \Exception;

final class DocumentationGeneratorFactory {

    public function createInstance(string $key, array $inputObjects = [], string $outputDirectory = null): ?DocumentationGenerator {
        
        $generators = DocumentationGenerators::get();
        
        if(!array_key_exists($key, $generators)) {
            
            throw new Exception("Invalid generator '{$key}' specified.");
        }
        
        $class = DocumentationGenerators::get()[$key];
        
        return new $class($inputObjects, $outputDirectory);
    }    
}
