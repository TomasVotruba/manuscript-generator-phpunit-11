<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Comment;
use ManuscriptGenerator\Markua\Parser\Node\Noop;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class RemoveCommentsNodeVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Comment) {
            return null;
        }

        return new Noop();
    }
}
