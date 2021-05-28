<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Visitor;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Document;

abstract class AbstractNodeVisitor implements NodeVisitor
{
    public function beforeTraversing(Document $document): void
    {
    }

    public function enterNode(Node $node): ?Node
    {
        return null;
    }

    public function afterTraversing(Document $document): void
    {
    }
}
