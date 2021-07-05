<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation;

/**
 * Description of Generator
 *
 * @author Justus
 */

abstract class DocumentationGenerator implements DocumentationGeneratorInterface {
    
    const PHAR_DIR = "./vendor/bin";
    
    private $inputObjects = [];
    private $outputDirectory = null;
    
    public function __construct(array $inputObjects = [], string $outputDirectory = null) {
     
        $this->inputObjects = [];
        
        foreach($inputObjects as $inputObject) {
        
            $inputObject = str_replace("/", DIRECTORY_SEPARATOR, $inputObject);
            
            if(is_dir($inputObject)) {
                
                $this->inputObjects[$inputObject . DIRECTORY_SEPARATOR] = true;
                continue;
            }
            
            if(is_file($inputObject)) {
                
                $this->inputObjects[$inputObject] = true;
                continue;
            }                        
            
            $this->inputObjects[$inputObject] = false;
        }
        
        $this->outputDirectory = $outputDirectory ?? getcwd() . DIRECTORY_SEPARATOR . $this->getKey();
    }    
    
    public function getInputObjects(): array {
        
        return $this->inputObjects;
    }
    
    public function getOutputDirectory(): string {
        
        return $this->outputDirectory;
    }
    
    final public function getInstanceKey(): string {
        
        return static::getClassKey();
    }        
    
    final public function getPath(): string {
        
        return str_replace("/", DIRECTORY_SEPARATOR, self::PHAR_DIR . DIRECTORY_SEPARATOR . $this->getFilename());
    }
    
    public function isDownloaded(): bool {
        
        $fn = $this->getPath();
        
        if(!file_exists($fn)) {
            
            return false;
        }
        
        return true;
    }    
    
    public function download(bool $ignoreCert): void {
        
        $uri = $this->getUri();
                
        $handle = curl_init();
        
        $fp = fopen($this->getPath(), 'w');

        $opts = [

            CURLOPT_FILE => $fp,
            CURLOPT_URL => $uri,
            CURLOPT_TIMEOUT => 3600, 
            CURLOPT_AUTOREFERER => true,
            //CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 4,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_HTTPAUTH => CURLAUTH_ANY
        ];        
        
        if($ignoreCert) {
            
            $opts[ CURLOPT_SSL_VERIFYPEER ] = false;
        }
        
        curl_setopt_array($handle, $opts);            

        curl_exec($handle);
        
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);          
        $error = curl_error($handle);
        
        curl_close($handle);
        fclose($fp);        
        
        if(!empty($error) || $code !== 200) {
            
            unlink($this->getFilename());
            
            throw new Exception("Error downloading file ('{$this->getPath()}'): {$error}.");
        }
        
        return;
    }    
}
