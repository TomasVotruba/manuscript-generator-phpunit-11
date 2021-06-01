<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;

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
