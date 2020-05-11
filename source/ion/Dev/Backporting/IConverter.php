<?php

namespace ion\Dev\Backporting;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface IConverter {
    
    function __construct(array $sourceVersion, array $targetVersion);
    
    function getSourceVersion(): array;
    
    function getTargetVersion(): array;

    function process(string $content): string;
}
