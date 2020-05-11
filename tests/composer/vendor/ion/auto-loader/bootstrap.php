<?php

/*
 * See license information at the package root in LICENSE.md
 */


if ((PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION < 6) || (PHP_MAJOR_VERSION < 5)) {
    die('This version of Auto-Loader is currently running on PHP version <em>' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '</em> - PHP version 5.6 or higher is required.');
}

$autoLoadPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

if (file_exists($autoLoadPath)) {
    require_once($autoLoadPath);
}

spl_autoload_register(function(/* string */ $className) {

    $dirs = [
        'build/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
        'build/' . PHP_MAJOR_VERSION,
        'build',
        'source'
    ];

    foreach($dirs as $dir) {
    
        $classPath = __DIR__ . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';

        //echo($classPath . '<br />');
        
        $classPath = realPath($classPath);
        
        if (file_exists($classPath)) {
            require_once($classPath);
            break;
        }
    }
    
    //exit;
}, true, true);

