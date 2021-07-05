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
use \ion\Dev\Documentation\Generators\PhpDocumentationGenerator;
use \ion\Dev\Documentation\DocumentationGeneratorFactory;

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
        
        $factory = new DocumentationGeneratorFactory();
        
        $generator = $factory->createInstance(
                
                $this->generator,
                $this->inputDirs,
                $this->outputDir
            );
        
        if(!$generator->isDownloaded()) {
            
            $this->output->write("Downloading generator PHAR binary from: {$generator->getUri()} ... ");
                        
            $generator->download($this->ignoreCert);

            if(!$generator->isDownloaded()) {
                
                throw new Exception("Could not find documentation generator binary ('{$generator->getFilename()}').");
            }

            $this->output->writeln("Done.");            
        } 
        
        $this->output->writeln("Found document generator binary: '{$generator->getFilename()}.'");

        $code = $generator->execute($this->output);
        
        if($code === 0) {
            
            $this->output->writeln("Done.");
            return $code;
        }
        
        $this->output->writeln("Failed!");
        return $code;
    }

}
