<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Visitors;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc\Method;

class CollectScopeMethodVisitor extends NodeVisitorAbstract
{
    /**
     * @var Method[]
     */
    private $methodList = [];

    /**
     * @var string
     */
    private $functionName = 'scopes';

    /**
     * @return Method[]
     */
    public function getMethodList(): array
    {
        return $this->methodList;
    }

    /**
     * @inheritDoc
     * @return int|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === $this->functionName) {
            $this->collectMethod($node);
            return NodeTraverser::STOP_TRAVERSAL;
        }
    }

    /**
     * @param Node\Stmt\ClassMethod $node
     * @return void
     */
    private function collectMethod(Node\Stmt\ClassMethod $node): void
    {
        foreach ($node->getStmts() as $stmt) {
            if (!$stmt instanceof Node\Stmt\Return_ && !$stmt->expr instanceof Node\Expr\Array_) {
                continue;
            }
            foreach ($stmt->expr->items as $item) {
                $method = new Method(
                    $item->key->value,
                    'self'
                );
                $this->methodList[] = $method;
            }
        }
    }
};
