<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Resource_ implements Node
{
    public function __construct(
        private string $link,
        private ?string $caption
    ) {
    }

    public function link(): string
    {
        return $this->link;
    }

    public function caption(): ?string
    {
        return $this->caption;
    }
}
