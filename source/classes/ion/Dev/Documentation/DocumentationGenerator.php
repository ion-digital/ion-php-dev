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

use \Symfony\Component\Console\Output\OutputInterface;

abstract class DocumentationGenerator implements DocumentationGeneratorInterface {
    
    const PHAR_DIR = "./vendor/bin";
    
    private $inputObjects = [];
    private $outputDirectory = null;
    private $overwriteOutput = false;
    private $overwriteProject = false;
    
    public function __construct(

            array $inputObjects = [],
            string $outputDirectory = null,
            bool $overwriteOutput = false,
            bool $overwriteProject = false
            
        ) {
     
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
        
//        var_dump($this->inputObjects);
//        exit;        
        
        $this->outputDirectory = $outputDirectory ?? getcwd() . DIRECTORY_SEPARATOR . $this->getKey();
        $this->overwriteOutput = $overwriteOutput;
        $this->overwriteProject = $overwriteProject;
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
    
    final public function getBinaryPath(): string {
        
        return str_replace("/", DIRECTORY_SEPARATOR, self::PHAR_DIR . DIRECTORY_SEPARATOR . $this->getBinaryFilename());
    }
    
    final public function execute(OutputInterface $output, bool $ignoreSslCert = false): int {
        
        if(!$this->isBinaryDownloaded()) {
            
            $output->write("Downloading generator PHAR binary from: {$this->getUri()} ... ");
                        
            $this->downloadBinary($ignoreSslCert);

            if(!$this->isBinaryDownloaded()) {
                
                throw new Exception("Could not find the documentation generator binary ('{$this->getBinaryPath()}').");
            }

            $output->writeln("Done.");            
        }         
        
        $output->writeln("Found document generator binary: '{$this->getBinaryPath()}.'");        
        
        $output->writeln("Objects to scan: \n\n\t -> " . implode("\n\t -> ", array_keys($this->getInputObjects())) . "\n");

        if(!file_exists($this->getProjectFilename()) || $this->overwriteProject) {
        
            $output->write(($this->overwriteProject ? "Overwriting project file" : "Creating project file") . " ... ");        
            $this->writeConfig();        
            $output->writeln("Done.");
        }
        
        if($this->overwriteOutput && is_dir($this->outputDirectory)) {
            
            $output->write("Removing output directory ... ");
            static::rmdir($this->outputDirectory);            
            $output->writeln("Done.");
        }
        
        $cmdOutput = [];
        $cmdResult = 0;
        
        $output->writeln("\nExecuting: {$this->prepareCommand()}\n");
        
        exec($this->prepareCommand(), $cmdOutput, $cmdResult);
        
        $output->writeln($cmdOutput);
        
        if($cmdResult === 0) {
            
            $output->writeln("Done.");
            return $cmdResult;
        }
        
        $output->writeln("Failed!");                
        return $cmdResult;
    }
    
    // Thanks to: itay@itgoldman.com (https://www.php.net/manual/en/function.rmdir.php#117354)
    
    private static function rmdir($src) {

        $dir = opendir($src);
        while (false !== ( $file = readdir($dir))) {
            
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                
                if (is_dir($full)) {
                    
                    static::rmdir($full);
                    
                } else {
                    
                    unlink($full);
                }
            }
        }
        
        closedir($dir);
        rmdir($src);
    }

    public function isBinaryDownloaded(): bool {
        
        $fn = $this->getBinaryPath();
        
        if(!file_exists($fn)) {
            
            return false;
        }
        
        return true;
    }    
    
    public function downloadBinary(bool $ignoreCert): void {
        
        $uri = $this->getUri();
                
        $handle = curl_init();
        
        $fp = fopen($this->getBinaryPath(), 'w');

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
            
            unlink($this->getBinaryFilename());
            
            throw new Exception("Error downloading file ('{$this->getBinaryPath()}'): {$error}.");
        }
        
        return;
    }    
}
