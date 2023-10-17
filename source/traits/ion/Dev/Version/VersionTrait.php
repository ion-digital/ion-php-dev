<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Version;

/**
 * Description of TLoadVersion
 *
 * @author Justus
 */

use \ion\Package;
use \ion\SemVerInterface;
use \ion\SemVer;
use \Exception;

trait VersionTrait {

    protected function saveVersion(SemVerInterface $version, string $optOrCmd, bool $print = true): int {
        
        $workingDir = getcwd();
        
        switch(strtolower($optOrCmd)) {
            
            case 'version': {
                
                $path = $workingDir . DIRECTORY_SEPARATOR . SemVer::ION_PACKAGE_VERSION_FILENAME;

                //TODO: Add a JSON output function to \ion\SemVerInterface $jsonObj = new \stdClass();

                $jsonObj->major = $version->getMajor();
                $jsonObj->minor = $version->getMinor();
                $jsonObj->patch = $version->getPatch();

                if($version->getRelease() !== null) {

                    $jsonObj->release = $version->getRelease();
                }

                if($version->getBuildData() !== null) {

                    $jsonObj->build = $version->getBuildData();
                }

                file_put_contents($path, json_encode($jsonObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                return 0;
            }
            case 'composer': {
                
                $path = $workingDir . DIRECTORY_SEPARATOR . 'composer.json';
                
                if(!file_exists($path)) {

                    throw new Exception("Composer definition file ('$path') does not exist.");                
                }
                
                $data = file_get_contents($path);

                if(!empty($data)) {
                    
                    $json = json_decode($data, false);
                    
                    if(json_last_error() !== JSON_ERROR_NONE) {
                        
                        return -2;
                    }
                    
                    $json->version = (string) $version;
                    
                    file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }                    

                return 0;
            }
            default: {
                
                if(empty($optOrCmd)) {
                    
                    throw new Exception("Could not execute '$optOrCmd'.");
                }
                
                $return = 0;
                $output = [];
                
                exec(str_replace('%s', (string) $version, $optOrCmd), $output, $return);
                        
                if(count($output) > 0 && !$print) {
                    
                    $this->write(join("\n", $output) . "\n");
                }
                
                return $return;
            }
            
        }    

        return 0;
    }
    
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
                
                $path = $workingDir . DIRECTORY_SEPARATOR . SemVer::ION_PACKAGE_VERSION_FILENAME;
                
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
