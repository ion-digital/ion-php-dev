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
    private $input;
    private $output;

    public function __construct(
            
        string $action,
        string $inputDir,
        string $outputDir,
        bool $overwrite,
        array $filenames = [],
        InputInterface $input = null,
        OutputInterface $output = null
            
    ) {
        
        $this->action = strtolower($action);
        $this->inputDir = $inputDir;
        $this->outputDir = $outputDir;
        $this->overwrite = $overwrite;
        $this->filenames = $filenames;
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
                
                $interfaceName = pathinfo(str_replace('*', $classFn, $fnTemplate))['filename'];
                
                $tmp = '';
                
                if($baseInputDir === null) {

                    $baseInputDir = realpath($inputDir);
                }                
                
                //$baseInputDir .= DIRECTORY_SEPARATOR;
                
                $cwd = realpath(getcwd());
                $tmp = str_replace($baseInputDir, "", $inputDir);
                
                $interfaceFn = str_replace('/', DIRECTORY_SEPARATOR, "{$outputDir}{$tmp}" . $interfaceName . ".php");
                
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
                            $firstFnTemplate
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
            string $firstFnTemplate
            
    ): ?string {
        
        
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($data);
        
        if($ast === null) {
            
            throw new Exception("Could not create PHP parser object.");
        }
        
        $traverser = new NodeTraverser();
       
        
        
//        $visitor = new class($interfaceName, $fnTemplate, $primary, $firstFnTemplate) extends NodeVisitorAbstract {
//
//            private $interfaceName;
//            private $remove;
//            private $inClass;
//            private $fnTemplate;
//            private $firstFnTemplate;
//            private $primary;
//            private $classCnt;
//
//            public function __construct(string $interfaceName, string $fnTemplate, bool $primary, string $firstFnTemplate = null) {
//
//                $this->interfaceName = $interfaceName;
//                $this->remove = false;
//                $this->inClass = false;
//                $this->fnTemplate = $fnTemplate;
//                $this->firstFnTemplate = $firstFnTemplate;
//                $this->primary = $primary;
//                $this->classCnt = 0;
//            }
//
//            private static function convertNode(Node $old, callable $converter, Node $new, bool $children = true): ?Stmt {
//
//                if($new === null) {
//                    
//                    return null;
//                }
//                
//                if($old->getDocComment() !== null) {
//                    
//                    $new->setDocComment(new Doc(str_replace($old->name, $new->name, $old->getDocComment()->getReformattedText())));
//                }                
//                
//                $converter($old, $new);               
//   
//                if(!$children) {
//
//                    return $new;
//                }
//
//                if(!is_array($old->stmts)) {
//
//                    return $new;
//                }
//
//                foreach($old->stmts as $stmt) {
//
//                    $new->stmts[] = $stmt;
//                }
//
//                return $new;
//            }
//
//            public function enterNode(Node $node) {
//
//                if($node instanceof Class_) {
//
//                    if($node->isAnonymous()) {
//                        
//                        return $node;
//                    }
//                    
//                    $this->classCnt++;
//                    
//                    if($this->primary) {
//                    
//                        return self::convertNode(
//                                
//                                $node, 
//                                 
//                                function(Class_ $old, Interface_ $new) {
//
//                              
//                            if($old->extends !== null ) {
//                                
//                                $tmp = str_replace("*", $old->extends->getLast(), $this->fnTemplate);
//                                $new->extends = [ new Name($tmp) ];
//                            }
//
//                            return;
//                            
//                        },
//                        new Interface_($this->interfaceName),
//                        true);
//                    }
//                    
//                    return self::convertNode(
//                            
//                            $node,                              
//                            function(Class_ $old, Interface_ $new) {
//
//                        $tmp = str_replace("*", $old->name, $this->firstFnTemplate);                        
//                        
//                        $new->setDocComment(new Doc("/**\n * {$new->name} is an alias for, and extends: {$tmp}\n\n */"));
//                        
//                        $new->extends = [ new Name($tmp) ];
//                        
//                        return;
//                        
//                    },
//                    new Interface_($this->interfaceName),
//                    false);                    
//                    
//                }
//
//                if($node instanceof ClassMethod) {
//
//                    if(!$node->isPublic()) {
//
//                        $this->remove = true;    
//                        
//                        //var_dump("\n\n$node->name\n\n");
//
//                        return null;
//                    }                    
//
//                    return self::convertNode(
//                        $node, 
//
//                        function(ClassMethod $old, ClassMethod $new) {
//
//                            $modifiers = 0;
//
//                            if($old->flags & Class_::MODIFIER_STATIC) {
//
//                                $modifiers = Class_::MODIFIER_STATIC;
//                            }
//
//                            $new->flags = $modifiers;
//                            $new->stmts = null;                        
//
//                            return;
//                        },
//                        $node);
//                }                                
//
//                return null;
//            }      
//
//            public function leaveNode(Node $node) {            
//                
//                if ($this->remove === true) {
//
//                    $this->remove = false;
//                    return NodeTraverser::REMOVE_NODE;
//                }
//
//                return null;
//            }            
//
//            public function getClassCount(): int {
//                
//                return $this->classCnt;
//            }
//        };
//        
//        $traverser->addVisitor($visitor);
        
        $result = $traverser->traverse($ast);
        
//        if($visitor->getClassCount() === 0) {
//            
//            return null;
//        }
        
        return "<?php\n" . (new PrettyPrinter($interfaceName, $fnTemplate))->prettyPrint($result);
    }
}

