<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Processor\Headlines;

use ManuscriptGenerator\Markua\Parser\Node;
use ManuscriptGenerator\Markua\Parser\Node\Heading;
use ManuscriptGenerator\Markua\Parser\Visitor\AbstractNodeVisitor;

final class CapitalizeHeadlinesNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private HeadlineCapitalizer $headlineCapitalizer,
        private bool $capitalizeHeadlines
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Heading) {
            return null;
        }

        if (! $this->capitalizeHeadlines) {
            return null;
        }

        $node->title = $this->headlineCapitalizer->capitalizeHeadline($node->title);

        return $node;
    }
}
