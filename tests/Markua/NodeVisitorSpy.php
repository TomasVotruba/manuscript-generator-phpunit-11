<?php

declare(strict_types=1);

namespace BookTools\Test\Markua;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Document;
use BookTools\Markua\Parser\Visitor\AbstractNodeVisitor;

final class NodeVisitorSpy extends AbstractNodeVisitor
{
    /**
     * @var array<string>
     */
    private array $calledMethods = [];

    public function beforeTraversing(Document $document): void
    {
        $this->calledMethods[] = 'beforeTraversing';
    }

    public function enterNode(Node $node): ?Node
    {
        $this->calledMethods[] = 'enterNode: ' . basename(str_replace('\\', '/', $node::class));

        return null;
    }

    public function afterTraversing(Document $document): void
    {
        $this->calledMethods[] = 'afterTraversing';
    }

    /**
     * @return array<string>
     */
    public function calledMethods(): array
    {
        return $this->calledMethods;
    }
}
