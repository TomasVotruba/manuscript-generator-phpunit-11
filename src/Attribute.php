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

    public function value(): string
    {
        return $this->value;
    }

    public function hasKey(string $key): bool
    {
        return $this->key === $key;
    }

    public function key(): string
    {
        return $this->key;
    }
}
