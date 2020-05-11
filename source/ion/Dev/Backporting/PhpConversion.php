<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ion\Dev\Backporting;

/**
 * Description of PhpConversion
 *
 * @author Justus
 */
abstract class PhpConversion implements IPhpConversion {
    
    private $converter;
    private $path;
    
    public function __construct(string $path, IConverter $converter) {
        
        $this->converter = $converter;
        $this->path = $path;
    }
    
    public function getPath(): string {
        return $this->path;
    }
    
    public function getConverter(): IConverter {
        return $this->converter;
    }
    
}
