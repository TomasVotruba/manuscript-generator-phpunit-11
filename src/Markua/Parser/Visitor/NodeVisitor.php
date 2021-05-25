<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Visitor;

use BookTools\Markua\Parser\Node;

interface NodeVisitor
{
    public function enterNode(Node $node): void;
}
