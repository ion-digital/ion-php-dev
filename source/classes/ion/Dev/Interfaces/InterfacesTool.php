<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;
use \PhpParser\Node;
use \PhpParser\Node\Stmt;
use \PhpParser\Node\Stmt\Function_;
use \PhpParser\Node\Stmt\ClassMethod;
use \PhpParser\Node\Stmt\Return_;
use \PhpParser\Node\Stmt\Class_;
use \PhpParser\Node\Stmt\Interface_;
use \PhpParser\Node\Stmt\Trait_;
use \PhpParser\Node\Scalar;
use \PhpParser\Node\NullableType;

use \PhpParser\Comment\Doc;

use \PhpParser\NodeVisitorAbstract;
use \PhpParser\Comment;
use \PhpParser\Node\Stmt\Use_;
use \PhpParser\Node\Stmt\UseUse;
use \PhpParser\Node\Name;
use \PhpParser\Node\Name\FullyQualified;
use \PhpParser\Node\Expr\Closure;
use \PhpParser\Node\Expr\Cast\Object_;
use \PhpParser\Node\Const_;
use \PhpParser\Node\Stmt\ClassConst;

use \PhpParser\BuilderFactory;


class InterfacesTool extends Tool {    
    
    private static function loadVendorFromComposer(): ?string {
        
        $path = getcwd() . DIRECTORY_SEPARATOR . "composer.json";
        $data = file_get_contents($path);
        
        if($data === false) {
            
            throw new Exception("I could not load composer.json - please specify a vendor.");
        }
        
        $json = json_decode($data);
        
        if(empty($json)) {
            
            throw new Exception("I could not parse composer.json - please specify a vendor.");
        }
        
        $name = $json->name;
        
        if(empty($name)) {
            
            throw new Exception("The name field in composer.json is empty - please specify a vendor.");
        }
        
        $segments = explode("/", $name);
        
        if(count($segments) === 0) {
            
            throw new Exception("The name field in composer.json does not contain a vendor (\"vendor/package\") - please specify a vendor.");
        }                
        
        return $segments[0];
    }
    
    private $action;
    private $inputDir;
    private $outputDir;
    private $overwrite;
    private $filenames;
    private $prefixesToStrip;
    private $suffixesToStrip;
    private $prefixesToIgnore;
    private $suffixesToIgnore;    
    private $namespaces;
    private $input;
    private $output;

    public function __construct(
            
        string $action,
        string $inputDir,
        string $outputDir,
        bool $overwrite,
        array $filenames = [],
        array $prefixesToStrip = [],
        array $suffixesToStrip = [],
        array $prefixesToIgnore = [],
        array $suffixesToIgnore = [],        
        bool $vendorOnly = null,
        string $vendor = null,
        InputInterface $input = null,
        OutputInterface $output = null       
            
    ) {
        
        $this->action = strtolower($action);
        $this->inputDir = $inputDir;
        $this->outputDir = $outputDir;
        $this->overwrite = $overwrite;
        $this->filenames = $filenames;
        $this->prefixesToStrip = $prefixesToStrip;
        $this->suffixesToStrip = $suffixesToStrip;
        $this->prefixesToIgnore = $prefixesToIgnore;
        $this->suffixesToIgnore = $suffixesToIgnore;         
        $this->namespaces = (!$vendorOnly ? null : (empty($vendor) ? [ static::loadVendorFromComposer() ] : [ $vendor ]));
        $this->input = $input;
        $this->output = $output;        
    }
    
    public function execute(): int {

        $inputDir = $this->inputDir;
        $outputDir = $this->outputDir;
        
        if(!is_dir($inputDir)) {
            
            throw new Exception("The input directory '{$inputDir}' doesn't exist!");
        }
        
        if(!is_dir($outputDir)) {
            
            mkdir($outputDir);
        }
        
        $this->processDirectory(
                
            $this->output, 
            $this->action, 
            $inputDir, 
            realpath($inputDir) . DIRECTORY_SEPARATOR, 
            $outputDir, 
            $this->filenames,
            $this->prefixesToStrip,
            $this->suffixesToStrip,
            $this->prefixesToIgnore,
            $this->suffixesToIgnore,      
            $this->namespaces,
            $this->overwrite
        );

        return 0;
    }
    

    
    private function processDirectory(
            
            OutputInterface $output, 
            string $action, 
            string $inputDir, 
            string $baseInputDir,
            string $outputDir, 
            array $templates = [], 
            array $prefixesToStrip = [],
            array $suffixesToStrip = [],
            array $prefixesToIgnore = [],
            array $suffixesToIgnore = [],
            array $namespaces = null,
            bool $overwrite = false            
            
    ): void {

        $absDir = realpath($inputDir) . DIRECTORY_SEPARATOR; 
        
        $objs = array_filter(scandir($absDir), function($item) {
            
            if($item === '.' || $item === '..' || empty($item)) {
                
                return false;
            }

            return true;            
        });
 
        foreach(array_values($objs) as $obj) {
            
            $path = realpath($absDir . DIRECTORY_SEPARATOR . $obj);
                       
            if(is_dir($path)) {
                
                $this->processDirectory(
                        
                    $output,
                    $action,
                    $path . DIRECTORY_SEPARATOR,
                    $baseInputDir,
                    $outputDir,
                    $templates,
                    $prefixesToStrip,
                    $suffixesToStrip,
                    $prefixesToIgnore,
                    $suffixesToIgnore,   
                    $namespaces,
                    $overwrite
                        
                );
                
                continue;
            }            
            
            if(!is_file($path)) {
                
                continue;
            }
            
            $this->processFile(
                    
                $output, 
                $action, 
                $inputDir, 
                $baseInputDir, 
                $outputDir, 
                $path,
                $overwrite, 
                $templates, 
                $prefixesToStrip, 
                $suffixesToStrip,
                $prefixesToIgnore, 
                $suffixesToIgnore,
                $namespaces
                    
            );
        }  
    }
    
    private function processFile(
            
            OutputInterface $output,              
            string $action,
            string $inputDir, 
            string $baseInputDir,
            string $outputDir, 
            string $path,
            bool $overwrite,
            array $templates,
            array $prefixesToStrip,
            array $suffixesToStrip,
            array $prefixesToIgnore,
            array $suffixesToIgnore,
            array $namespaces = null
            
    ): void {

        // Remove .php extension from templates

        foreach($templates as &$template) {

            if(!str_ends_with($template, ".php")) {
                
                continue;
            }
            
            $template = substr($template, 0, strlen($template) - 4);
        }        
        
        $model = InterfaceModel::parseData(
                
            file_get_contents($path), 
            $templates, 
            $prefixesToStrip, 
            $suffixesToStrip, 
            $prefixesToIgnore, 
            $suffixesToIgnore,
            $namespaces
        );
        
        $memory = [];
        
        if(!$model->hasStructName()) {
            
            $output->writeln("No class or trait found in file '{$path}.'");
            return;
        }
        
//            echo "\n\n=========================\n\n";
////            var_dump($model->getStructName());
//            var_dump($model->getStructName()->getInterfaceVariations(
//                
//                $templates,
//                $prefixesToStrip,
//                $suffixesToStrip,
//                $prefixesToIgnore,
//                $suffixesToIgnore
//                
//            ));
//            var_dump($prefixesToStrip);
//            var_dump($suffixesToStrip);
//            var_dump($prefixesToIgnore);
//            var_dump($suffixesToIgnore);
//            echo "\n\n=========================\n\n";        
        
        //echo "\n\n--> {$path} {$model->getStructName()}\n\n";
        
        $variations = $model->getStructName()->getInterfaceVariations(
                
            $templates,
            $prefixesToStrip,
            $suffixesToStrip,
            $prefixesToIgnore,
            $suffixesToIgnore
        );
        
        if(count($variations) === 0) {
            
            $output->writeln("No interface variations generated for file '{$path}.'");
            return;
        }        
        
        foreach($variations as $cnt => $interfaceName) {       
            
            //echo "\n\n--X {$interfaceName}\n\n";
            
            $outputPath = str_replace('/', DIRECTORY_SEPARATOR, "{$outputDir}" 
                        . str_replace($baseInputDir, "", $inputDir))
                        . "{$interfaceName->getModifiedName($prefixesToStrip, $suffixesToStrip, $prefixesToIgnore, $suffixesToIgnore)->getName()}.php";
                        

//            exit;
                        
            if($action === 'generate') { 
                
                $output->write("Generating '{$interfaceName}' ('{$outputPath}') from '{$model->getStructName()}' ... ");

                try {

                    if(!$overwrite && file_exists($outputPath)) {

                        throw new Exception("File already exists - specify --overwrite to override.");
                    }                                                

                    if(!is_dir(dirname($outputPath))) {

                        mkdir(dirname($outputPath), 0777, true);
                    }    

                    $tmp = $model->generate($interfaceName->getName(), $memory, $cnt);
                    
                    if(empty($tmp)) {

                        $output->writeln("No class definitions found to generate - skipping.");
                        continue;
                    }

                    file_put_contents(
                            
                        $outputPath, 
                        $tmp
                    );  

                    $output->writeln("Done.");

                }
                catch(Throwable $ex) {

                    $output->writeln("Error: {$ex->getMessage()}");

                    $trace = $ex->getTrace();

                    if(is_countable($trace) && count($trace) > 0) {

                        $output->writeln("");

                        foreach([$trace[0]] as $index => $item) {

                            if(!array_key_exists("file", $item) || !array_key_exists("line", $item)) {

                                continue;
                            }

                            $output->writeln("Stack trace: {$item['file']} @ line {$item['line']}");

                        }

                        $output->writeln("");                            
                    }
                }                
                
                continue;
            }
            
            if($action === 'clean') {
                
                $inputPath = $inputDir . "{$interfaceName->getName()}.php";                
                
                echo "FIXME: $inputPath\n";
                
                $output->write("Cleaning '" . pathinfo($inputPath, PATHINFO_BASENAME) . "' ... ");                                        

                if(!file_exists($inputPath)) {

                    $output->writeln("Done (file did not exist!).");
                    continue;
                }

//                if(unlink($inputPath)) {
//
//                    $output->writeln("Done.");
//                    continue;
//                }

                $output->writeln("Error: Could not delete file!");
                
                continue;
            }                        
        }
        
        return;        
    }            
}

