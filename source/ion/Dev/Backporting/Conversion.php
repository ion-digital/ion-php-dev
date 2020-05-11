<?php

namespace ion\Dev\Backporting;

/**
 * Description of Conversion
 *
 * @author Justus
 */
use \PhpParser\Node;
use \PhpParser\Node\Stmt\Function_;
use \PhpParser\Node\Stmt\ClassMethod;
use \PhpParser\Node\Stmt\Return_;
use \PhpParser\Node\Stmt\Class_;
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

class Conversion {

    public static function insertFunctionTypeComments(Node $node) {

        $return = null;
        $params = [];

        if ($node instanceof ClassMethod || $node instanceof Function_) {

            foreach ($node->getParams() as $param) {
                $params[$param->name] = $param->type;
            }
        }

        $lines = [(property_exists(Node::class, 'name') ? $node->name . ' ' : '' ) . ($node instanceof ClassMethod ? 'method' : 'function'), ''];

        if (count(array_keys($params)) > 0) {
            foreach ($params as $name => $type) {
                //$lines[] = "@param $type \$$name";
            }
            $lines[] = '';
        }

        $returnType = null;

        if ($node->returnType === null) {
            $returnType = 'mixed';
        } else if ($node->returnType instanceof NullableType) {
            $returnType = '?' . $node->returnType->type;
        } else {
            $returnType = $node->returnType;
        }


        $lines[] = '@return ' . $returnType;

        if ($node->getDocComment() === null) {
            $node->setDocComment(new Doc("/**\n" . ' * ' . join("\n * ", $lines) . "\n */"));
        }

        return $node;
    }

    public static function convertTo56(array &$ast, int $sourceMajorVersion, int $sourceMinorVersion = 0): array {

        $ast = static::convertTo70($ast, $sourceMajorVersion, $sourceMinorVersion);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new class extends NodeVisitorAbstract {

            private $remove = false;

            public function __construct() {
                $this->remove = false;
            }

            public function enterNode(Node $node) {

                if ($node instanceof Function_ || $node instanceof ClassMethod || $node instanceof Closure) {



                    if ($node instanceof Function_ || $node instanceof ClassMethod) {
                        Conversion::insertFunctionTypeComments($node);
                    }

                    // Remove return values completely

                    $node->returnType = null;

                    foreach ($node->getParams() as $paramNode) {

                        if ($paramNode->type instanceof NullableType) {
                            $paramNode->type = $paramNode->type->type;
                        }

                        switch (strtolower($paramNode->type)) {
                            case 'bool':
                            case 'float':
                            case 'string':
                            case 'int': {
                                    $paramNode->type = null;
                                    break;
                                }
                            default:
                        }
                    }
                }

                if ($node instanceof Use_) {

                    $uses = [];

                    foreach ($node->uses as $useUse) {
                        $name = $useUse->name;

                        //var_dump($name);

                        if (!in_array('Throwable', $name->parts) && $useUse->alias !== 'Throwable') {
                            $uses[] = $useUse;
                        } else {
                            $uses[] = new UseUse(new FullyQualified('Exception'), 'Throwable');
                        }
                    }

                    $node->uses = $uses;

                    if (count($uses) === 0) {
                        $this->remove = true;
                    }
                }


                return null;
            }

            public function leaveNode(Node $node) {

                if ($this->remove === true) {
                    $this->remove = false;
                    return NodeTraverser::REMOVE_NODE;
                }

                return null;
            }
        });



        $ast = $traverser->traverse($ast);

        //file_put_contents('output.log', ob_get_contents());

        return $ast;
    }

    public static function convertTo70(array &$ast, int $sourceMajorVersion, int $sourceMinorVersion = 0): array {

        $ast = static::convertTo71($ast, $sourceMajorVersion, $sourceMinorVersion);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract {

            public function enterNode(Node $node) {

                if ($node instanceof Function_ || $node instanceof ClassMethod || $node instanceof Closure) {

                    if ($node instanceof Function_ || $node instanceof ClassMethod) {
                        Conversion::insertFunctionTypeComments($node);
                    }

                    if ($node->returnType instanceof NullableType) {

                        $node->returnType = null;
                    } else if (is_string($node->returnType)) {

                        if ($node->returnType === 'void') {
                            $node->returnType = null;
                        }
                    }

                    foreach ($node->getParams() as $paramNode) {

                        if ($paramNode->type instanceof NullableType) {
                            $paramNode->type = $paramNode->type->type;
                        }
                    }
                } 
            }
        });

        $ast = $traverser->traverse($ast);

        return $ast;
    }

    public static function convertTo71(array &$ast, int $sourceMajorVersion, int $sourceMinorVersion = 0): array {

        $ast = static::convertTo72($ast, $sourceMajorVersion, $sourceMinorVersion);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract {

            public function enterNode(Node $node) {

                if ($node instanceof Function_ || $node instanceof ClassMethod || $node instanceof Closure) {

                    if ($node instanceof Function_ || $node instanceof ClassMethod) {
                        Conversion::insertFunctionTypeComments($node);
                    }

                    // Remove 'object' return values completely

                    $tmp1 = $node->returnType;

                    if ($tmp1 instanceof NullableType) {
                        $tmp1 = $node->returnType->type;
                    }

                    if (is_string($tmp1)) {

                        if ($tmp1 === 'object') {
                            $node->returnType = null;
                        }
                    }

                    foreach ($node->getParams() as $paramNode) {

                        $tmp2 = $paramNode->type;

                        if ($tmp2 instanceof NullableType) {
                            $tmp2 = $paramNode->type->type;
                        }

                        switch (strtolower($tmp2)) {
                            case 'object': {
                                    $paramNode->type = null;
                                    break;
                                }
                            default:
                        }
                    }
                } else if (isset($node->expr) && $node->expr instanceof Object_) {
                    $node->expr = $node->expr->expr;
                }
            
                else if($node instanceof ClassConst) {

                    // Remove 'private,' 'protected' and 'public' keywords from class constants (PHP 7.1 and lower)
                    if(isset($node->flags)) {
                    
                        $node->flags &= ~Class_::MODIFIER_PRIVATE;
                        $node->flags &= ~Class_::MODIFIER_PROTECTED;
                        $node->flags &= ~Class_::MODIFIER_PUBLIC;
                    }
                    
                }
            }
        });

        $ast = $traverser->traverse($ast);

        return $ast;
    }

    public static function convertTo72(array &$ast, int $sourceMajorVersion, int $sourceMinorVersion = 0): array {

        // PHP 7.2 is the current base version so it stays unconverted

        return $ast;
    }

}
