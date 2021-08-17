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
use \PhpParser\NodeTraverser;
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
use \PhpParser\ParserFactory;
use \PhpParser\BuilderFactory;


class InterfacesTool extends Tool {

    private $action;
    private $inputDir;
    private $outputDir;
    private $overwrite;
    private $filenames;
    private $prefixesToStrip;
    private $suffixesToStrip;
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
            array $fnTemplates = [], 
            array $prefixesToStrip = [],
            array $suffixesToStrip = [],
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
                    $fnTemplates,
                    $prefixesToStrip,
                    $suffixesToStrip,
                    $overwrite
                        
                );
                
                continue;
            }            
            
            if(!is_file($path)) {
                
                continue;
            }
            
            $pi = pathinfo($path); 
            $classFn = $pi['filename'];  
            $classBn = $pi['basename'];
            
            $firstFnTemplate = null;
            
            if(count($fnTemplates) > 0) {
                
                $firstFnTemplate = $fnTemplates[0];
                $firstFnTemplate = str_replace(".php", "", $firstFnTemplate);
            }
            
            foreach($fnTemplates as $index => &$fnTemplate) {
                                
                $fnTemplate = str_replace(".php", "", $fnTemplate);
            }
            
            foreach($fnTemplates as $index => &$fnTemplate) {
                
                
                //$fnTemplate = str_replace(".php", "", $fnTemplate);
                
                $tmpFn = $classFn;
                
                $tmp = '';
                
                if($baseInputDir === null) {

                    $baseInputDir = realpath($inputDir);
                }                
                
                //$baseInputDir .= DIRECTORY_SEPARATOR;
                
                $cwd = realpath(getcwd());
                $tmp = str_replace($baseInputDir, "", $inputDir);

                foreach($prefixesToStrip as $prefix) {

                    if(!preg_match("/^([{$prefix}])/", $tmpFn)) {

                        continue;
                    }

                    $tmpFn = preg_replace("/^([{$prefix}])/", '', $tmpFn, 1);  
                    break;
                }
                
//                var_dump($classFn);

                foreach($suffixesToStrip as $suffix) {

                    if(!preg_match("/({$suffix})\$/", $tmpFn)) {

                        continue;
                    }

                    $tmpFn = preg_replace("/({$suffix})\$/", '', $tmpFn, 1);
                    break;
                }   
                
                //var_dump($classFn);
                
                
                
                $interfaceName = pathinfo(str_replace('*', $tmpFn, $fnTemplate))['filename'];
                $interfaceFn = str_replace('/', DIRECTORY_SEPARATOR, "{$outputDir}{$tmp}" . $interfaceName . ".php");
                
                //var_dump($interfaceName);
//                var_dump($interfaceFn);
//                
//                echo "\n\$path:\t\t\t {$path}\n\$baseInputDir:\t\t {$baseInputDir}\n\$inputDir:\t\t {$inputDir}\n\$cwd:\t\t\t {$cwd}\n\$interfaceName:\t\t {$interfaceName}\n\$tmp:\t\t {$tmp}\n\$interfaceFn:\t\t {$interfaceFn}\n\n";                
//                continue;
                
                if($action === 'clean') {
                    
                    $inputFn = "{$inputDir}" . $interfaceName . ".php";
                    
                    $output->write("Cleaning '{$inputFn}' ... ");                                        
                    
                    if(!file_exists($inputFn)) {
                        
                        $output->writeln("Done (file did not exist!).");
                        continue;
                    }
                    
                    if(unlink($inputFn)) {
                        
                        $output->writeln("Done.");
                        continue;
                    }
                    
                    $output->writeln("Error: Could not delete file!");
                    
                    continue;
                }
                
                if($action === 'generate') {                                   
                
                    $from = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', "{$inputDir}{$classBn}");
                    
                    $output->write("Generating '{$interfaceFn}' ({$interfaceName}) from '{$from}' ... ");
                    
                    
                    
                    try {
                        
                        if(!$overwrite && file_exists($path)) {
                            
                            throw new Exception("File already exists - specify --overwrite to override.");
                        }                                                
                        
                        if(!is_dir(dirname($interfaceFn))) {
                            
                            mkdir(dirname($interfaceFn), 0777, true);
                        }
                        
                        $tmp = $this->processFile(
                                
                            $interfaceName, 
                            $output, 
                            $fnTemplate,                                
                            file_get_contents($path), 
                            $index === 0,
                            $fnTemplates,
                            $prefixesToStrip,
                            $suffixesToStrip
                        );           
                        
                        if($tmp === null) {
                            
                            $output->writeln("No class definitions found - skipping.");
                            continue;
                        }
                        
                        file_put_contents(
                            $interfaceFn, 
                            $tmp
                        );  
                        
                        $output->writeln("Done.");
                    }
                    catch(Exception $ex) {
                        
                        $output->writeln("Error: {$ex->getMessage()}");
                                                   
                        $trace = $ex->getTrace();
                        
                        if(is_countable($trace) && count($trace) > 0) {
                            
                            $output->writeln("");
                            
                            foreach([$trace[0]] as $index => $item) {
                                
//["file"]=>  string(54) "/....../test.php"
//["line"]=>  int(37)
//["function"]=>  string(11) "__construct"
//["class"]=>  string(4) "Test"
//["type"]=>  string(2) "->"
//["args"]=>  array(0) { }                                
                          
                                $output->writeln("Stack trace: {$item['file']} @ line {$item['line']}");
                                
                            }

                            $output->writeln("");                            
                        }
                        


                    }
                    
                    continue;
                }
            }
        }
    }
    
    private function processFile(
            
            string $interfaceName, 
            OutputInterface $output, 
            string $fnTemplate, 
            string $data, 
            bool $primary,
            array $fnTemplates,
            array $prefixesToStrip,
            array $suffixesToStrip
            
    ): ?string {
        
        
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($data);
        
        if($ast === null) {
            
            throw new Exception("Could not create PHP parser object.");
        }
        
        $traverser = new NodeTraverser();

        $result = $traverser->traverse($ast);

        return "<?php\n" . (new InterfacePrettyPrinter($fnTemplate, $primary, $fnTemplates, $prefixesToStrip, $suffixesToStrip))->prettyPrint($result);
    }
}

