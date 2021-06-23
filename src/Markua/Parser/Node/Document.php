<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use ManuscriptGenerator\Markua\Parser\Node;

final class Document extends AbstractNode
{
    /**
     * @param array<Node> $nodes
     * @param array<Node> $virtualNodes
     */
    public function __construct(
        public array $nodes,
        public array $virtualNodes
    ) {
    }

    public function subnodeNames(): array
    {
        return ['nodes', 'virtualNodes'];
    }
}
