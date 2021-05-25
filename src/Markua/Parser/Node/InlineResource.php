<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

final class InlineResource extends AbstractNode
{
    public Attributes $attributes;

    public function __construct(
        public string $contents,
        public ?string $format = null,
        ?Attributes $attributes = null
    ) {
        $this->attributes = $attributes === null ? new Attributes() : $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
