<?php


namespace ion\Dev\Backporting;

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
use \PhpParser\Node\Name\FullyQualified;
use \PhpParser\Node\Name\Relative;
use \PhpParser\Comment;
use \PhpParser\Comment\Doc;

class PrettyPrinter extends Standard {
    
    protected function pName_Relative(Relative $node) {
        return 'namespace \\' . implode('\\', $node->parts);
    }
    
    protected function p(Node $node) {
    
        // Classes / Interfaces
        
        if($node instanceof Class_ || $node instanceof Interface_) {
            return "\n" . parent::p($node);
        }
        
        // Namespaces
        
        if($node instanceof Namespace_ || $node instanceof FullyQualified || $node instanceof Relative) {
            //return "\n" . parent::p($node);
        }        
        
        // Functions / Methods
        
        if($node instanceof Function_ || $node instanceof ClassMethod) {
            return ( $node->getDocComment() === null ? parent::p($node) . "\n" : "\n" . parent::p($node) . "\n");
        }           
        
        // Comments
        
        if($node instanceof Comment || $node instanceof Doc) {
            return parent::p($node) . "\n";
        }
        
        return parent::p($node);
    }
}
