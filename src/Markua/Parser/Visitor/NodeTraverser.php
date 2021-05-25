<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Visitor;

use BookTools\Markua\Parser\Node;

final class NodeTraverser
{
    /**
     * @param array<NodeVisitor> $nodeVisitors
     */
    public function __construct(
        private array $nodeVisitors
    ) {
    }

    /**
     * @param array<Node>|Node $nodes
     */
    public function traverse(mixed $nodes): void
    {
        if (! is_array($nodes)) {
            $nodes = [$nodes];
        }

        foreach ($nodes as $node) {
            foreach ($this->nodeVisitors as $nodeVisitor) {
                $nodeVisitor->enterNode($node);
            }

            $this->traverse($node->subnodes());
        }
    }
}
