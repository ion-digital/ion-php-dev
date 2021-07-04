<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion;

/**
 * Description of Dev
 *
 * @author Justus
 */
class Dev {
    
//    public static function getCallingPath(): string {
//
//        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
//
//        if (static::count($trace) === 0 || (static::count($trace) > 0 && !array_key_exists('file', $trace[count($trace) - 1]))) {
//
//            throw new PhpHelperException("Could not determine the calling method / function's file path (i.e. the code file that contains it).");
//        }
//
//        return realpath($trace[count($trace) - 1]['file']);
//    }
//
//    public static function getCallingClass(): string {
//
//        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
//
//        if (static::count($trace) === 0 || (static::count($trace) > 0 && !array_key_exists('class', $trace[count($trace) - 1]))) {
//
//            throw new PhpHelperException("Could not determine the calling class' name.");
//        }
//
//        return $trace[count($trace) - 1]['class'];
//    }
    
    private static function getCallingCode(): object {
        
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        
        
    }
    
    public static function break() {
        
        $line = 1;
        $file = "";
        
        echo static::class . ": break() @ {$file}, line {$line}.";
        exit;
    }    
    
    
    public static function watch($variable) {
        
        
    }      

    public static function dump($variable, callable $breakOnValue = null) {
        
        
    }
  
    

}
