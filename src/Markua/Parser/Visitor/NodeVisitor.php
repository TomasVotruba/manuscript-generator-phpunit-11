<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Visitor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Document;
use ManuscriptGenerator\Markua\Parser\Node\Noop;

interface NodeVisitor
{
    public function beforeTraversing(Document $document): void;

    /**
     * A node visitor can modify properties of the provided node directly. If the visitor doesn't want to do anything,
     * it can return null, or just return the node as-is. A visitor can also choose to return a new node which will
     * replace the provided node in the tree. Returning a Noop node will effectively delete the node.
     *
     * @see Noop
     */
    public function enterNode(Node $node): ?Node;

    public function afterTraversing(Document $document): void;
}
