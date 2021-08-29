<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\TraitUse;
/**
 * Description of NodeVisitor
 *
 * @author Justus
 */


class NodeVisitor extends NodeVisitorAbstract {
 
    private $model;
    
    public function __construct(InterfaceModel $model) {
        
        $this->model = $model;
    }
    
    public function leaveNode(Node $node) {
        
        $modelName = $this->model->getName();

        if($modelName === null) {

            $modelName = new NameModel();
        }            
        
        if($node instanceof Namespace_) {
            
            $modelName->setNamespaceParts(explode("\\", $node->name));
            
            $this->model->setName($modelName);
            
            return null;
        }

        if($node instanceof Class_ || $node instanceof Trait_) {
            
            $modelName->setName($node->name);
        
            $this->model->setName($modelName);
            
            if(!empty($node->getDocComment())) {
                
                $this->model->setDoc($node->getDocComment());
            }
            
            if($node instanceof Class_) {
                
                if(!empty($node->extends)) {

                    $this->model->setParent(NameModel::getFromParts($node->extends->parts, true));                
                }       
            
                if(is_countable($node->implements) && count($node->implements) > 0) {

                    foreach($node->implements as $implements) {

                        $this->model->addInterface(NameModel::getFromParts($implements->parts, true));
                    }
                }    
            }
            
            return null;
        }
        
        if($node instanceof Use_) {
            
            foreach($node->uses as $use) {                                
                
                if(!is_countable($use->name->parts)) {

                    continue;
                } 
                
                $this->model->addReference(NameModel::getFromParts($use->name->parts, true));
            }
            
            return null;
        }
        

        
        if($node instanceof TraitUse) { 
            
            foreach($node->traits as $trait) {                              
                
                if(!is_countable($trait->parts)) {

                    continue;
                }                 
                
                if(count($trait->parts) === 0) {
                    
                    continue;
                }
                
                $this->model->addTrait(NameModel::getFromParts($trait->parts, true));
            }
            
            return null;
        }
        
        if($node instanceof ClassMethod) {
            
            if(!$node->isPublic() || $node->name == '__construct') {
                
                return null;
            }
            
            $method = new MethodModel($node->name);
            
            $this->model->addMethod($method);
            
            if(!empty($node->getDocComment())) {
            
                $method->setDoc($node->getDocComment());
            }

            if($node->isStatic()) {
                
                $method->setStatic(true);
            }
            
            if($node->returnsByRef()) {
                
                $method->setReturnsByReference(true);
            }
            
            foreach($node->getParams() as $nodeParam) {
                
                $param = new MethodParameterModel($nodeParam->name);
                
                if(!empty($nodeParam->type)) {
                    
                    $type = $nodeParam->type;
                    $nullable = false;
                    
                    if($type instanceof NullableType) {

                        $nullable = true;

                        $type = $type->type;
                    }

                    if(is_string($type)) {

                        $param->setType(new TypeModel(new NameModel(null, $type), $nullable));

                    } else if($type instanceof Name) {

                        $param->setType(new TypeModel(NameModel::getFromParts($type->parts, true), $nullable));
                    }
                }
                
                if($nodeParam->byRef) {
                    
                    $param->setByReference(true);
                }
                
                if($nodeParam->variadic) {
                    
                    $param->setVariadic(true);
                }
                
                if(!empty($nodeParam->getDocComment())) {
                    
                    $param->setDoc($nodeParam->getDocComment());
                }
                
                if(!empty($nodeParam->default) && !$nodeParam->variadic) {

                    if($nodeParam->default instanceof ConstFetch) {

                        $param->setDefault($nodeParam->default->name, MethodParameterModel::DEFAULT_TYPE_CONST);
                    }

                    else if($nodeParam->default instanceof Array_) {

                        $param->setDefault("[ " . implode(", ", $nodeParam->default->items) . " ]", MethodParameterModel::DEFAULT_TYPE_ARRAY);
                    }

                    else if($nodeParam->default instanceof Scalar) {

                        if($nodeParam->default instanceof String_) {

                            $param->setDefault($nodeParam->default->value, MethodParameterModel::DEFAULT_TYPE_STRING);
                        }
                        else {

                            $param->setDefault($nodeParam->default->value, MethodParameterModel::DEFAULT_TYPE_SCALAR);
                        }                    
                    }

                    else if ($nodeParam->default instanceof ClassConstFetch) {

                        $tmp = "";
                        
                        if(!empty($nodeParam->default->class)) {

                            $tmp .= $nodeParam->default->class . "::";
                        }

                        $param->setDefault("{$tmp}{$nodeParam->default->name}", MethodParameterModel::DEFAULT_TYPE_CLASS);
                    }

                    else {

                        $param->setDefault("null", MethodParameterModel::DEFAULT_TYPE_NULL);               
                    }
                }   
                
                $method->addParameter($param);
            }
                  
            if(!empty($node->getReturnType())) {
                
                $returnType = $node->getReturnType();
                $nullable = false;
                
                if($returnType instanceof NullableType) {
                    
                    $nullable = true;
                    
                    $returnType = $returnType->type;
                }
 
                if(is_string($returnType)) {
                    
                    $method->setReturnType(new TypeModel(new NameModel(null, $returnType), $nullable));
                    
                } else if($returnType instanceof Name) {

                    $method->setReturnType(new TypeModel(NameModel::getFromParts($returnType->parts, true), $nullable));
                }
            }            
                               
            return null;
        }

        
    }
}