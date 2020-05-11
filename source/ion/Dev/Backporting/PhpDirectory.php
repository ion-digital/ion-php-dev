<?php


namespace ion\Dev\Backporting;

/**
 * Description of PhpNameSpace
 *
 * @author Justus
 */

class PhpDirectory extends PhpConversion {
    
    private $recursive;
    
    public function __construct(string $dir, Converter $converter, bool $recursive = true) {
       
        parent::__construct($dir, $converter);
        
        $this->recursive = $recursive;
        
        
        
    }
    
    public function isRecursive(): bool {
        return $this->recursive;
    }
    
    public function convert(string $target): void {
        
        //echo 'INPUT: ' . $this->getPath() . ' OUTPUT: ' . $target . "\n";
        
        $dirs = array_values(glob($this->getPath() . '*', GLOB_ONLYDIR));
        
        foreach($dirs as $dir) {            
            
            $relativeDir = str_replace($this->getPath(), '', $dir) . DIRECTORY_SEPARATOR;
            
            $inputPath = $dir . DIRECTORY_SEPARATOR;   
            $outputPath = $target . $relativeDir;

            $conversion = new PhpDirectory($inputPath, $this->getConverter(), $this->isRecursive());            
            $conversion->convert($outputPath);

            if(is_dir($outputPath) === false) {
                mkdir($outputPath, 0777, true);
            }
        }
        
        //echo "Scanning " . $this->getPath() . "\n";
        
        if(is_dir($target) === false) {
            mkdir($target, 0777, true);
        }        
        
        $files = scandir($this->getPath(), SCANDIR_SORT_NONE);

        foreach($files as $filename) {

            if($filename !== '.' && $filename !== '..') {
                
                $inputFile = $this->getPath() . $filename;
                $outputFile = $target . $filename;
                                
                if(is_file($inputFile) === true) {                    
                    $file = new PhpFile($inputFile, $this->getConverter());
                    $file->convert($outputFile);
                }
                
            }
            
        }
                          
        
    }    
    
}
