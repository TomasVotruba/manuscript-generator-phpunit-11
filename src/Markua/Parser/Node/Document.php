<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

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
