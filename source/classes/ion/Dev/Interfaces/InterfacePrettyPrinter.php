<?php


namespace ion\Dev\Interfaces;

/**
 * Description of PrettyPrinter
 *
 * @author Justus
 */

use \PhpParser\PrettyPrinter\Standard;
use \PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use \PhpParser\Node\Stmt\ClassMethod;
use \PhpParser\Node\Stmt\Class_;
use \PhpParser\Node\Stmt\Trait_;
use \PhpParser\Node\Stmt\Interface_;
use \PhpParser\Node\Stmt\Namespace_;
use \PhpParser\Node\Stmt\Use_;
use \PhpParser\Node\Stmt\UseUse;
use \PhpParser\Node\Name\FullyQualified;
use \PhpParser\Node\Name\Relative;
use \PhpParser\Comment;
use \PhpParser\Comment\Doc;
use \PhpParser\Node\NullableType;
use \PhpParser\Node\Param;
use \PhpParser\Node\Expr\ConstFetch;
use \PhpParser\Node\Expr\Array_;
use \PhpParser\Node\Scalar\String_;
use \PhpParser\Node\Scalar;
use \PhpParser\Node\Expr\ClassConstFetch;
use \PhpParser\Node\Stmt\ClassConst;
use \PhpParser\Node\Stmt\Const_;
use \PhpParser\Node\Name;
use \PhpParser\Node\Scalar\DNumber;
use \PhpParser\Node\Scalar\LNumber;
    
class InterfacePrettyPrinter extends Standard {
            
    private const PHP_CLASSES = [
      
        'Directory',
        'stdClass',
        '__PHP_Incomplete_Class',
        'Exception',
        'ErrorException',
        'php_user_filter',
        'Closure',
        'Generator',
        'ArithmeticError',
        'AssertionError',
        'DivisionByZeroError',
        'Error',
        'Throwable',
        'ParseError',
        'TypeError',
        'Traversable',
        'Iterator',
        'IteratorAggregate',
        'Throwable',
        'ArrayAccess',
        'Serializable',
        'WeakReference',
        'WeakMap',
        'Stringable',
        'DateTime',
        'DateInterval'        
    ];
    
    private const RESERVED = [
      
        "string",
        "float",
        "int",
        "bool"
    ];    
    
    private static $uses = [];
    private static $containers = [];
    
    private static function indent(string $s, bool $tabs = false, int $indents = 4): string {
        
        $lines = explode("\n", $s);
        
        $result = [];
        
        foreach($lines as $line) {
            
            $tmp = "";
            
            if(strlen($line) > 0) {

                for($cnt = 0; $cnt < $indents; $cnt++) {

                    $tmp .= $tabs ? "\t" : " ";
                }
            }
            
            $result[] = $tmp . trim($line);
        }
        
        return implode("\n", $result);
    }
    
    private static function applyTemplate(string $name, string $template): string {
        
        return str_replace("*", $name, $template);
    }
    
    private static function isPhpClass(string $className) {
        
        if(in_array($className, self::PHP_CLASSES) || (array_key_exists($className, static::$uses) && count(static::$uses[$className]->parts) <= 1)) {
            
            return true;
        }
        
        return false;
    }
    

    
    private $fnTemplate;
    private $fnTemplates;
    private $primary;
    private $indents;
    private $tabs;    
    private $prefixesToStrip;
    private $suffixesToStrip;
    
    public function __construct(
            
        string $fnTemplate, 
        bool $primary,
        array $fnTemplates,
        array $prefixesToStrip,
        array $suffixesToStrip,
        bool $tabs = false, 
        int $indents = 4
            
    ) {
               
        $this->fnTemplate = $fnTemplate;
        $this->fnTemplates = $fnTemplates;
        $this->primary = $primary;
        $this->tabs = $tabs;
        $this->indents = $indents;
        $this->prefixesToStrip = $prefixesToStrip;
        $this->suffixesToStrip = $suffixesToStrip;
    }
    
    protected function p($node) {                   

        $php = "";
        
        if($node instanceof Namespace_) {

            $php .= "namespace {$node->name};\n\n";
            
            foreach($node->stmts as $stmt) {
                
                $php .= $this->p($stmt);
            }
            
            return $php;
        }     
        
        if($node instanceof Use_) {
            
            foreach($node->uses as $use) {
                              
                $ns = "";                
                
                if(is_countable($use->name->parts)) {
                    
                    if(count($use->name->parts) > 1) {
                        
                        $ns = "\\";
                    }
                    
                    $ns = implode("\\", array_splice($use->name->parts, 0, count($use->name->parts) - 1)) . "\\";
                }
                
                $tmpBase = [ $use->name->getLast() ];
                
                
                
                foreach($this->fnTemplates as $index => $fnTemplate) {
                    
                    $pattern = "/^" . implode('([A-Za-z_]+)', explode("*", $fnTemplate)) . "\$/";
                    
                    $matches = [];
                    
                    if(!preg_match($pattern, $use->name->getLast(), $matches)) {
                        
                        continue;
                    }                   
                    
                    if(!in_array($matches[1], $tmpBase)) {
                        
                        $tmpBase[] = $matches[1];
                    }                        
                }
                
                foreach($this->fnTemplates as $index => $fnTemplate) {
                    
                    $pattern = "/^" . implode('([A-Za-z_]+)', explode("*", $fnTemplate)) . "\$/";
                    
                    $matches = [];
                    
                    $tmp = str_replace("*", $use->name->getLast(), $fnTemplate);
                    
                    //$tmp = null;
                    
                    if(preg_match($pattern, $use->name->getLast(), $matches)) {
                                                
                        $tmp = str_replace("*", $matches[1], $fnTemplate);
                    }

                    if($tmp === null) {
                        
                        continue;
                    }            
                    
                    if(!in_array($tmp, $tmpBase)) {
                        
                        $tmpBase[] = $tmp;
                    }                    

                }                                
                
                $tmpBase = array_filter($tmpBase, function($val) {
                    
                    $matchCnt = 0;
                    
                    foreach($this->fnTemplates as $fnTemplate) {
                        
                        $pattern = "/^" . implode('([A-Za-z_]+)', explode("*", $fnTemplate)) . "\$/";
                        
                        if(preg_match($pattern, $val)) {
                            
                            $matchCnt++;
                        }
                    }
                    
                    if($matchCnt > 1) {
                        
                        return false;
                    }
                    
                    if(in_array(strtolower($val), self::RESERVED) || in_array($val, self::PHP_CLASSES)) {
                        
                        return false;
                    }
                    
                    return true;
                });
                
                foreach($tmpBase as $tmp) {
                    
                    //$php .= "use {$ns}{$tmp};\n";
                    
                    static::$containers[] = "{$ns}{$tmp}";
                }
                
                static::$uses[$use->name->getLast()] = $use->name;
            }
            
            return "$php";
        }
        
        if($node instanceof Class_ || $node instanceof Trait_) {
            
            $tmpName = $node->name;
            
            foreach(array_unique(static::$containers) as $use) {
                
                $php .= "use {$use};\n";
            }
            
            foreach($this->prefixesToStrip as $prefix) {
                
                if(!preg_match("/^({$prefix})/", $tmpName)) {
                    
                    continue;
                }
                
                $tmpName = preg_replace("/^({$prefix})/", '', $tmpName, 1);                
                break;
            }
            
            foreach($this->suffixesToStrip as $suffix) {
                
                if(!preg_match("/({$suffix})\$/", $tmpName)) {
                    
                    continue;
                }
                
                $tmpName = preg_replace("/({$suffix})\$/", '', $tmpName, 1);
                break;
            }
            
            $interfaceName = static::applyTemplate($tmpName, $this->fnTemplate);

            $php .= "\n";
            
            if($this->isPrimary()) {
                            
                $php .= "{$node->getDocComment()}\n";
            }
            else {
                
                $php .= "/**\n *\n * This interface is an alias for " . static::applyTemplate($tmpName, $this->fnTemplates[0]) . ".\n *\n */\n";
            }
            
            $php .= "\ninterface {$interfaceName}";
            
            $extends = [];        

            if($this->isPrimary()) {


                if($node instanceof Class_ && !empty($node->extends)) {

                    if(!static::isPhpClass($node->extends->toString())) {
                        
                        $extends[] = static::applyTemplate($node->extends->toString(), $this->fnTemplate);
                    }
                }

                if($node instanceof Class_ && is_countable($node->implements) && count($node->implements) > 0) {

                    foreach($node->implements as $implements) {

                        if($implements->toString() == $interfaceName) {

                            continue;
                        }

                        if(!static::isPhpClass($implements->toString())) {
                            
                            $extends[] = $implements->toString();
                        }
                    }
                }
                
                if(count($this->fnTemplates) > 1) {
                                        
                    foreach(array_slice($this->fnTemplates, 1) as $fnTemplate) {
                        
                        $tmp = static::applyTemplate($tmpName, $fnTemplate);
                        
                        if(in_array($tmp, $extends)) {
                            
                            continue;
                        }
                        
                        $extends[] = $tmp;
                    }
                }
                
            }
            
            $php .= (count($extends) > 0 ? " extends " . implode(", ", $extends) : "") . " {\n";

            if($this->isPrimary()) {
                
                foreach($node->stmts as $stmt) {

                    $php .= $this->p($stmt);
                }
                
            } else {
                
                $php .= "\n" . static::indent("// No method definitions! Please see: " . static::applyTemplate($tmpName, $this->fnTemplates[0]) . ".\n\n");
            }
            
            $php .= "}\n";
                
            return $php;
        }
        
        if($node instanceof ClassMethod) {

            if(!$node->isPublic()) {
                
                return $php;
            }
            
            if($node->name == '__construct') {
                
                return $php;
            }

            if(!empty($node->getDocComment())) {
            
                $php .= "\n{$node->getDocComment()}\n";
            }
            
            $php .= "\n";
            
            if($node->isStatic()) {
                
                $php .= "static ";
            }

            $php .= "function {$node->name}(";

            $params = [];
            
            foreach($node->getParams() as $param) {
                
                $params[] = $this->p($param);        
            }

            if(!empty($params)) {
                
                $php .= implode(", ", $params);
            }
            
            $php .= ")";
            
            if(!empty($node->getReturnType())) {
                
                $php .= ": ";
                
                if($node->getReturnType() instanceof NullableType) {
                    
                    $php .= "?{$node->getReturnType()->type}";
                    
                } else if(is_string($node->getReturnType())) {
                    
                    $php .= $node->getReturnType();
                    
                } else if($node->getReturnType() instanceof Name) {
                    
                    $php .= implode("\\", $node->getReturnType()->parts);
                }
            }
            
            $php .= ";\n";
            
            return static::indent($php, $this->tabs, $this->indents);
        }
        
        if($node instanceof Param) {              
            
            if($node->type !== null) {
                
                $php .= "{$node->type} ";
            }
            
            if($node->variadic) {
                
                $php .= "...";
            }
            
            $php .= ($node->byRef === true ? "&" : "") . "\${$node->name}";
            
            if(!empty($node->default) && !$node->variadic) {
            
                $php .= " = ";
                
                if($node->default instanceof ConstFetch) {

                    $php .= "{$node->default->name}";                    
                }

                else if($node->default instanceof Array_) {

                    $php .= "[" . implode(", ", $node->default->items) . "]";

                }
                
                else if($node->default instanceof Scalar) {
                    
                    if($node->default instanceof String_) {

                        $php .= "\"{$node->default->value}\"";
                    }
                    else {

                        $php .= $node->default->value;
                    }                    
                }
                
                else if ($node->default instanceof ClassConstFetch) {

                    if(!empty($node->default->class)) {
                    
                        $php .= $node->default->class . "::";
                    }
                    
                    $php .= $node->default->name;
                }
                
                else {
                    
                    $php .= "null";                
                }
            }             
            
            return $php;
        }             
        
//        if($node instanceof ClassConst) {
//            
//            $php = "";
//            
//            foreach($node->consts as $const) {
//
//                if($const->value === null) {
//                    
//                    continue;
//                }
//                
//                if($const->value instanceof String_) {
//                    
//                    $php .= "const {$const->name} = '{$const->value->value}';\n";
//                    continue;
//                }
//                
//                if($const->value instanceof DNumber || $const->value instanceof LNumber) {
//                    
//                    $php .= "const {$const->name} = {$const->value->value};\n";
//                    continue;
//                }                
//    
//                //$php .= "const {$const->name} = {$const->value->name};\n";
//            }
//            
//            return static::indent($php, $this->tabs, $this->indents);
//            
//        }
        
        return "";
    }
    
    protected function isPrimary(): bool {
        
        return $this->primary;
    }
}
