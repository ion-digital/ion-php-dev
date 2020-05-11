#!/usr/bin/env php
<?php

/*
 * See license information at the package root in LICENSE.md
 */
 
if(file_exists('../autoload.php')) {
    require_once('../autoload.php');
} else {
    if(file_exists('../vendor/autoload.php')) {
        require_once('../vendor/autoload.php');
    } else {
        die("Could not find Composer's autoload.php - aborting.");
    }    
}

