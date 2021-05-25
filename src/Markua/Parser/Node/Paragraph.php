<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

final class Paragraph implements Node
{
    public function __construct(
        public string $text
    ) {
    }

    public function subnodeNames(): array
    {
        return [];
    }
}
