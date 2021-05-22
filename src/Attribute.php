<?php

declare(strict_types=1);

namespace BookTools;

final class Attribute
{
    public function __construct(
        private string $key,
        private string $value
    ) {
    }

    public static function quoted(string $key, string $value): self
    {
        return new self($key, '"' . addslashes($value) . '"');
    }

    public function asString(): string
    {
        return $this->key . ': ' . $this->value;
    }
}
