<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Visitors;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc\ClassPhpDocBuilder;

class AddClassDocVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassPhpDocBuilder
     */
    private $builder;

    /**
     * @param ClassPhpDocBuilder $builder
     */
    public function __construct(ClassPhpDocBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->createPhpDoc($node);
            return NodeTraverser::STOP_TRAVERSAL;
        }
        return null;
    }

    /**
     * @param Node\Stmt\Class_ $node
     * @return void
     */
    private function createPhpDoc(Node\Stmt\Class_ $node): void
    {
        $comments = $node->getComments();
        if (isset($comments[0])) {
            $this->builder->setComment($comments[0]);
        } else {
            $this->builder->setDefaultComment($node->name->toString());
        }

        $this->builder->addPropertyDoc();
        $this->builder->addMethodDoc();
        $doc = $this->builder->getDoc();

        $node->setDocComment($doc);
    }
};
