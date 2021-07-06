<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation\Generators;

/**
 *
 * @author Justus
 */

use \ion\Dev\Documentation\DocumentationGenerator;
use \Exception;
use Symfony\Component\Console\Output\OutputInterface;

class PhpDocDocumentationGenerator extends DocumentationGenerator {
    
    private const KEY = "phpdoc";
    private const URI = "https://phpdoc.org/phpDocumentor.phar";   
    private const CONFIG_FILENAME = "phpdoc.xml";
    
    public static function getClassKey(): string {
        
        return self::KEY;
    }
    
    public function getUri(): string {
  
        return self::URI;
    }    
    
    public function getBinaryFilename(): ?string {
        
        return pathinfo($this->getUri())['basename'];
    }        
    
    public function getProjectFilename(): ?string {
        
        return self::CONFIG_FILENAME;
    }
    
    public function prepareCommand(): string {
                        
        //return "php -B \"error_reporting(E_ERROR | E_PARSE);\" {$this->getBinaryPath()} --config " . $this->getProjectFilename();  
        return "php {$this->getBinaryPath()} --config " . $this->getProjectFilename();  
    }
    
    protected function writeConfig(): void {
        
        $packageName = null;

        if(file_exists("composer.json")) {
            
            $composerJson = json_decode(file_get_contents("composer.json"));
            
            $packageName = $composerJson->name;
        }
        
        $title = (empty($packageName) ? "" : "<title>{$packageName}</title>");                
        
        $sources = "";
        
        foreach($this->getInputObjects() as $obj => $exists) {
            
            if(!$exists) {
                
                continue;
            }
            
            $sources .= "\t\t\t\t<path>" . str_replace(DIRECTORY_SEPARATOR, '/', $obj) . "</path>\n";
        }
                
        $output = $this->getOutputDirectory();
        
        
        file_put_contents(self::CONFIG_FILENAME, trim(<<<XML
               
<?xml version="1.0" encoding="UTF-8" ?>
<phpdocumentor
        configVersion="3"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns="https://www.phpdoc.org"
        xsi:noNamespaceSchemaLocation="data/xsd/phpdoc.xsd"
>
{$title}
    <paths>

      <output>{$output}</output>
      
    </paths>
    <version number="latest">
    
        <folder>latest/</folder>
    
        <api>
            <source dsn=".">
{$sources}
            </source>
    
            <output>package/</output>
       
            <ignore-tags>
                <ignore-tag>template</ignore-tag>
                <ignore-tag>template-extends</ignore-tag>
                <ignore-tag>template-implements</ignore-tag>
                <ignore-tag>extends</ignore-tag>
                <ignore-tag>implements</ignore-tag>
            </ignore-tags>
    
            <default-package-name>{$packageName}</default-package-name>
            
            <visibility>public</visibility>
            <visibility>protected</visibility>
            
        </api>
        <guide>
            
            <source dsn=".">
                
            </source>

        </guide>
    </version>
    <template name="default"/>
</phpdocumentor>   
 
XML
        ) . "\n");
    }
    
    
}