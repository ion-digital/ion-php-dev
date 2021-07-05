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

abstract class Generators {

    public const PHPDOC_URI = "https://phpdoc.org/phpDocumentor.phar";
    public const PHPDOX_URI = "http://phpdox.de/releases/phpdox.phar";
    public const PHPDOC_CMD = "php ./phpDocumentor.phar";
    public const PHPDOX_CMD = "php ./phpdox.phar";
    
    public const GENERATORS = [
        
        'phpdoc' => [ 
            
            'uri' => self::PHPDOC_URI,
            'cmd' => self::PHPDOC_CMD            
   
        ],
        'phpdox' => [
            
            'uri' => self::PHPDOX_URI,
            'cmd' => self::PHPDOX_CMD
        ]
    ];
}
