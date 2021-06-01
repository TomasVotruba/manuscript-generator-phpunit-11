<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;

interface NodeVisitor
{
    public function beforeTraversing(Document $document): void;

    /**
     * A node visitor can modify properties of the provided node directly. If the visitor doesn't want to do anything,
     * it can just return the node as it is. A visitor can also choose to return a new node which will replace the
     * provided node in the tree.
     */
    public function enterNode(Node $node): ?Node;

    public function afterTraversing(Document $document): void;
}
