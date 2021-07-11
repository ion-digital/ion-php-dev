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

class PrettyPrinter extends Standard {
    
//    protected function pName_Relative(Relative $node) {
//        
//        return 'namespace \\' . implode('\\', $node->parts);
//    }
    
    private $interfaceName;
    private $fnTemplate;
    
    public function __construct(string $interfaceName, string $fnTemplate) {
               
        $this->interfaceName = $interfaceName;
        $this->fnTemplate = $fnTemplate;
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
            
            $tmp = str_replace("*", $node->name, $this->fnTemplate);
            
            $php = "{$node->getDocComment()}\n";
            
            $php = "\ninterface {$tmp}";
            
            if(is_countable($node->extends) && count($node->extends) > 0) {
                
                foreach($node->extends as $extends) {

                    $php .= "$extends";
                }
                
                $php .= " ";
            }
            
            $php .= "{\n\n";
            
            foreach($node->stmts as $stmt) {
                
                $php .= $this->p($stmt);
            }
            
            $php .= "}\n";
                
            return $php;
        }
        
        if($node instanceof ClassMethod) {
            
            if(!$node->isPublic()) {
                
                return "";
            }
            
            $php = "{$node->getDocComment()}\n";
            
            $php .= "// $node->name\n";
            
            return $php;
        }
        
        return "";
    }
}
