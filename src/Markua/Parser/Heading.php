<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Heading implements Node
{
    public Attributes $attributes;

    public function __construct(
        public int $level,
        public string $title,
        ?Attributes $attributes = null
    ) {
        if ($attributes === null) {
            $attributes = new Attributes([]);
        }

        $this->attributes = $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
