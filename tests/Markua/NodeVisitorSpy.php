<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Visitor\NodeVisitor;

final class NodeVisitorSpy implements NodeVisitor
{
    /**
     * @var array<string>
     */
    private array $calledMethods = [];

    public function enterNode(Node $node): Node
    {
        $this->calledMethods[] = 'enterNode: ' . basename(str_replace('\\', '/', $node::class));
        return $node;
    }

    /**
     * @return array<string>
     */
    public function calledMethods(): array
    {
        return $this->calledMethods;
    }
}
