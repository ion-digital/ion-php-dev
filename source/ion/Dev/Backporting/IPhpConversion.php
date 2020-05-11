<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ion\Dev\Backporting;

interface IPhpConversion {
    
    function convert(string $target);
    
    function getPath(): string;
    
    function getConverter(): IConverter;
    
}