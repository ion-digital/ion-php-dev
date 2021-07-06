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
    private $overwriteOutput;
    private $overwriteProject;
    private $ignoreCert;
    private $input;
    private $output;
    private $download;

    public function __construct(
            
        string $generator,
        array $inputDirs,
        string $outputDir,
        bool $overwriteOutput,
        bool $overwriteProject,
        bool $download,
        bool $ignoreCert,
        InputInterface $input = null,
        OutputInterface $output = null
            
    ) {
        
        $this->generator = strtolower($generator);
        $this->inputDirs = $inputDirs;
        $this->outputDir = $outputDir;
        $this->overwriteOutput = $overwriteOutput;
        $this->overwriteProject = $overwriteProject;
        $this->download = $download;
        $this->ignoreCert = $ignoreCert;
        $this->input = $input;
        $this->output = $output;
        
        
    }    
    
    public function execute(): int {
        
        $factory = new DocumentationGeneratorFactory();
        
        return $factory->createInstance(
                
            $this->generator,
            $this->inputDirs,
            $this->outputDir,
            $this->overwriteOutput, 
            $this->overwriteProject

        )->execute($this->output);
    }

}
