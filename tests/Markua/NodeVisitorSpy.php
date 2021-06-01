<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Test\Markua;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

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
