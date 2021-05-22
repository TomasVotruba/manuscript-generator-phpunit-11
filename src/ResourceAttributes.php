<?php

declare(strict_types=1);

namespace BookTools;

final class ResourceAttributes
{
    /**
     * @param array<Attribute> $attributes
     */
    public function __construct(
        private array $attributes = []
    )
    {
    }

    public function withAttribute(Attribute $attribute): self
    {
        return new self(array_merge($this->attributes, [$attribute]));
    }

    public static function fromString(string $string): self
    {
        return new self([]);
    }

    public function asString(): string
    {
        $attributes = array_map(fn (Attribute $attribute) => $attribute->asString(), $this->attributes);

        return '{' . implode(', ', $attributes) . '}';
    }
}
