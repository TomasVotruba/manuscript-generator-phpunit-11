<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Resource_ implements Node
{
    public Attributes $attributes;

    public function __construct(
        public string $link,
        public ?string $caption,
        ?Attributes $attributes = null
    ) {
        if ($attributes === null) {
            $attributes = new Attributes();
        }

        $this->attributes = $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
