<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Visitors;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc\Property;

class CollectRelationClassVisitor extends NodeVisitorAbstract
{
    /**
     * @var Property[]
     */
    private $propertyList = [];

    /**
     * @var string
     */
    private $relationFunctionName = 'relations';

    /**
     * @var string[]
     */
    private const ARRAY_TYPE = [
        'MANY_MANY',
        'HAS_MANY'
    ];

    /**
     * @return Property[]
     */
    public function getPropertyList(): array
    {
        return $this->propertyList;
    }

    /**
     * @inheritDoc
     * @return int|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === $this->relationFunctionName) {
            $this->collectProperty($node);
            return NodeTraverser::STOP_TRAVERSAL;
        }
    }

    /**
     * @param Node\Stmt\ClassMethod $node
     * @return void
     */
    private function collectProperty(Node\Stmt\ClassMethod $node): void
    {
        foreach ($node->getStmts() as $stmt) {
            if (!$stmt instanceof Node\Stmt\Return_ && !$stmt->expr instanceof Node\Expr\Array_) {
                continue;
            }
            foreach ($stmt->expr->items as $item) {
                $relationNode = $item->value->items[1]->value;
                $type = $this->getType($relationNode);
                if (in_array($item->value->items[0]->value->name->toString(), self::ARRAY_TYPE, true)) {
                    $type .= '[]';
                } else {
                    $type .= '|null';
                }
                $property = new Property(
                    $item->key->value,
                    $type
                );
                $this->propertyList[] = $property;
            }
        }
    }

    /**
     * @param NodeAbstract $node
     * @return string
     */
    private function getType(NodeAbstract $node): string
    {
        if ($node instanceof Node\Scalar\String_) {
            return $node->value;
        } elseif ($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Node\Name) {
            return $node->class->toString();
        }
        return '';
    }
};
