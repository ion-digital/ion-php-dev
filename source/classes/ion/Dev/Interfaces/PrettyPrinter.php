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

class PrettyPrinter extends Standard {
            

    
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
    
    private $fnTemplate;
    private $firstFnTemplate;
    private $primary;
    private $indents;
    private $tabs;    
    
    public function __construct(
            
        string $fnTemplate, 
        bool $primary,
        string $firstFnTemplate,
        bool $tabs = false, 
        int $indents = 4
            
    ) {
               
        $this->fnTemplate = $fnTemplate;
        $this->firstFnTemplate = $firstFnTemplate;
        $this->primary = $primary;
        $this->tabs = $tabs;
        $this->indents = $indents;
    }
    
    protected function p($node) {                   

        if($node instanceof Namespace_) {

            $php = "namespace {$node->name};\n\n";
            
            foreach($node->stmts as $stmt) {
                
                $php .= $this->p($stmt);
            }
            
            return $php;
        }     
        
        if($node instanceof Use_) {
            
            $php = "use ";
            
            foreach($node->uses as $use) {
                
                $php .= $use->name;
            }
            
            return "$php;\n";
        }
        
        if($node instanceof Class_) {
            
            $interfaceName = static::applyTemplate($node->name, $this->fnTemplate);
            
            $php = "\n";
            
            if($this->isPrimary()) {
                            
                $php .= "{$node->getDocComment()}\n";
            }
            else {
                
                $php .= "/**\n *\n * This interface is an alias for " . static::applyTemplate($node->name, $this->firstFnTemplate) . ".\n *\n */\n";
            }
            
            $php .= "\ninterface {$interfaceName}";
            
            $extends = [];        

            if($this->isPrimary()) {


                if(!empty($node->extends)) {

                    $extends[] = static::applyTemplate($node->extends->toString(), $this->fnTemplate);
                }

                if(is_countable($node->implements) && count($node->implements) > 0) {

                    foreach($node->implements as $implements) {

                        if($implements->toString() == $interfaceName) {

                            continue;
                        }

                        $extends[] = $implements->toString();
                    }
                }
                
            } else {
                
                $extends[] = static::applyTemplate($node->name, $this->firstFnTemplate);
            }
            
            $php .= (count($extends) > 0 ? " extends " . implode(", ", $extends) : "") . " {\n\n";

            if($this->isPrimary()) {
                
                foreach($node->stmts as $stmt) {

                    $php .= $this->p($stmt);
                }
                
            } else {
                
                $php .= static::indent("// No method definitions! Please see: " . static::applyTemplate($node->name, $this->firstFnTemplate) . ".\n\n");
            }
            
            $php .= "}\n";
                
            return $php;
        }
        
        if($node instanceof ClassMethod) {
            
            $php = "";
            
            if(!$node->isPublic()) {
                
                return $php;
            }

            if(!empty($node->getDocComment())) {
            
                $php .= "{$node->getDocComment()}\n\n";
            }
            
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
                }
            }
            
            $php .= ";\n\n";
            
            return static::indent($php, $this->tabs, $this->indents);
        }
        
        if($node instanceof Param) {

            $php = "";                      
            
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
                
                else {

                    $php .= "null";                
                }
            }             
            
            return $php;
        }
        

        
        return "";
    }
    
    protected function isPrimary(): bool {
        
        return $this->primary;
    }
}
