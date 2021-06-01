<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use ManuscriptGenerator\Markua\Parser\Node;

final class Document extends AbstractNode
{
    /**
     * @param array<Node> $nodes
     */
    public function __construct(
        public array $nodes
    ) {
    }

    public function subnodeNames(): array
    {
        return ['nodes'];
    }
}
