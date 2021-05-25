<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

final class Directive implements Node
{
    public function __construct(
        public string $name
    ) {
    }

    public function subnodeNames(): array
    {
        return [];
    }
}
