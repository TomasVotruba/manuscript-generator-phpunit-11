<?php

declare(strict_types=1);

namespace BookTools\Markua\Processor\Headlines;

use BookTools\Markua\Parser\Node;
use BookTools\Markua\Parser\Node\Heading;
use BookTools\Markua\Parser\Visitor\NodeVisitor;

final class CapitalizeHeadlinesNodeVisitor implements NodeVisitor
{
    public function __construct(
        private HeadlineCapitalizer $headlineCapitalizer,
        private bool $capitalizeHeadlines
    ) {
    }

    public function enterNode(Node $node): Node
    {
        if (! $node instanceof Heading) {
            return $node;
        }

        if (! $this->capitalizeHeadlines) {
            return $node;
        }

        $node->title = $this->headlineCapitalizer->capitalizeHeadline($node->title);

        return $node;
    }
}
