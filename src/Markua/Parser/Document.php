<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Document implements Node
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
