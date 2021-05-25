<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

final class IncludedResource implements Node
{
    public Attributes $attributes;

    public function __construct(
        public string $link,
        public ?string $caption,
        ?Attributes $attributes = null
    ) {
        $this->attributes = $attributes === null ? new Attributes() : $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
