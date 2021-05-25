<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

final class IncludedResource extends AbstractNode
{
    public AttributeList $attributes;

    public function __construct(
        public string $link,
        public ?string $caption,
        ?AttributeList $attributes = null
    ) {
        $this->attributes = $attributes === null ? new AttributeList() : $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}