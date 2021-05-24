<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Resource_ implements Node
{
    public function __construct(
        public string $link,
        public ?string $caption,
        public ?Attributes $attributes = null
    ) {
    }
}
