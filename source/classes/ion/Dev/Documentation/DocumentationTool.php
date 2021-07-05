<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;

class DocumentationTool extends Tool {

    private $generator;
    private $inputDirs;
    private $outputDir;
    private $overwrite;
    private $ignoreCert;
    private $input;
    private $output;
    private $download;

    public function __construct(
            
        string $generator,
        array $inputDirs,
        string $outputDir,
        bool $overwrite,
        bool $download,
        bool $ignoreCert,
        InputInterface $input = null,
        OutputInterface $output = null
            
    ) {
        
        $this->generator = strtolower($generator);
        $this->inputDirs = $inputDirs;
        $this->outputDir = $outputDir;
        $this->overwrite = $overwrite;
        $this->download = $download;
        $this->ignoreCert = $ignoreCert;
        $this->input = $input;
        $this->output = $output;
        
        
    }    
    
    public function execute(): int {

        $cwd = realpath(getcwd()) . DIRECTORY_SEPARATOR;
        
        $generator = $this->generator;
        
        if(!array_key_exists($generator, Generators::GENERATORS)) {
            
            throw new Exception("Invalid generator '{$generator}' specified.");
        }
        
        $outputPath = "{$cwd}{$this->getBinaryFilename($generator)}";
         
        if(!$this->isBinaryDownloaded($generator) || $this->download) {
            
            $this->downloadGenerator($generator, $cwd, $outputPath, $this->ignoreCert, $this->output);
            
        } else {
            
            $this->output->writeln("Found generator binary: {$this->getBinaryFilename($generator)}");
        }
        
        if(!file_exists($outputPath)) {
            
            throw new Exception("Could not find generator binary '{$outputPath}.'");
        }
        
        
        
        $this->output->write("Executing '{$this->getBinaryCmd($generator)}' ... ");
        
        //exec($this->getBinaryCmd($generator));
        $this->output->writeln($this->getBinaryCmd($generator));
        $this->output->writeln(" Done.");
        
        return 0;
    }
        
    private function downloadGenerator(string $generator, string $cwd, string $outputPath, bool $ignoreCert, OutputInterface $output): void {
        
        $output->write("Downloading generator PHAR binary from: {$this->getBinaryUri($generator)} ... ");
        
        $result = $this->downloadBinary($generator, $outputPath, $ignoreCert);
        
        if($result !== null) {
            
            throw new Exception("Error downloading file ({$result}).");
        }
        
        $output->writeln("Done (saved to '{$outputPath}').");
        
        return;
    }
    
    private function getBinaryUri(string $generator): ?string {
  
        if(!array_key_exists($generator, Generators::GENERATORS)) {
            
            return null;
        }
        
        if(!array_key_exists('uri', Generators::GENERATORS[$generator])) {
            
            return null;
        }        
        
        return Generators::GENERATORS[$generator]['uri'];
    }
    
    private function getBinaryCmd(string $generator): ?string {
  
        if(!array_key_exists($generator, Generators::GENERATORS)) {
            
            return null;
        }
        
        if(!array_key_exists('cmd', Generators::GENERATORS[$generator])) {
            
            return null;
        }        
        
        return Generators::GENERATORS[$generator]['cmd'];
    }    
    
    private function getBinaryFilename(string $generator): ?string {
        
        $fn = $this->getBinaryUri($generator);
        
        if($fn === null) {
            
            return null;
        }               
        
        return pathinfo($fn)['basename'];
    }
    
    private function isBinaryDownloaded(string $generator): bool {
        
        $fn = $this->getBinaryFilename($generator);
        
        if(!file_exists($fn)) {
            
            return false;
        }
        
        return true;
    }
    
    private function downloadBinary(string $generator, string $outputFn, bool $ignoreCert): ?string {
        
        $uri = $this->getBinaryUri($generator);
                
        $handle = curl_init();
        
        $fp = fopen("{$outputFn}", 'w');

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
        
        if(!empty($error)) {
            
            return $error;
        }
        
        return null;
    }
}
