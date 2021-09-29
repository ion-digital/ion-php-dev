<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Vcs;

/**
 * Description of TLoadVersion
 *
 * @author Justus
 */

use \ion\Package;
use \ion\ISemVer;
use \ion\SemVer;
use \Exception;

trait TVersion {

    protected function loadVersion(string $option = null): ?SemVerInterface {
        
        $version = null;
        
        $workingDir = getcwd();
        
        switch(strtolower($option)) {
            
            case null:
            case 'auto': {
                $version = $this->loadVersion('version', false);
                
                if($version === null) {
                    
                    $version = $this->loadVersion('composer', true);
                }
                break;
            }
            case 'version': {
                
                $path = $workingDir . DIRECTORY_SEPARATOR . Package::ION_PACKAGE_VERSION_FILENAME;
                
                if(!file_exists($path)&& $option !== 'auto') {
                    
                    throw new Exception("Version definition file ('$path') does not exist.");
                }
                
                if(file_exists($path)) {
                    $data = file_get_contents($path);

                    if($data) {

                        $version = SemVer::parsePackageJson($data);
                    }                                
                }
            
                break;
            }
            case 'composer': {
                
                $path = $workingDir . DIRECTORY_SEPARATOR . 'composer.json';
                
                if(!file_exists($path)) {

                    throw new Exception("Composer definition file ('$path') does not exist.");                
                }
                
                $data = file_get_contents($path);

                if($data) {
                    $version = SemVer::parseComposerJson($data);
                }                    
                             
                
                break;
            }
            default: {
                
                $version = SemVer::parse($option);

                if($version === null) {
                    
                    throw new Exception("Version string ('$option') could not be parsed into a valid SemVer instance.");                   
                }
            }
            
        }    
        
        return $version;
    }

    
    
}
