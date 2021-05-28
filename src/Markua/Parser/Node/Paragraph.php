<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

final class Paragraph extends AbstractNode
{
    /**
     * @param array<Node> $parts
     */
    public function __construct(
        public array $parts
    ) {
    }

    public function subnodeNames(): array
    {
        return ['parts'];
    }
}
