<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

final class Heading implements Node
{
    public Attributes $attributes;

    public function __construct(
        public int $level,
        public string $title,
        ?Attributes $attributes = null
    ) {
        $this->attributes = $attributes === null ? new Attributes() : $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
