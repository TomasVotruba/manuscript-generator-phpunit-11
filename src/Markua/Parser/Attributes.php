<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Attributes implements Node
{
    /**
     * @param array<Attribute> $attributes
     */
    public function __construct(
        public array $attributes = []
    ) {
    }

    public function setAttribute(string $key, string $value): void
    {
        foreach ($this->attributes as $index => $existingAttribute) {
            if ($existingAttribute->key === $key) {
                $this->attributes[$index]->value = $value;
                return;
            }
        }

        $this->attributes[] = new Attribute($key, $value);
    }

    public function valueOf(string $key): ?string
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->key === $key) {
                return $attribute->value;
            }
        }

        return null;
    }

    public function asMarkua(): string
    {
        return '{'
            . implode(
                ', ',
                array_map(
                    fn (Attribute $attribute) => $attribute->key . ': ' . $attribute->value,
                    $this->attributes
                )
            )
            . '}';
    }

    public function removeAttribute(string $key): void
    {
        foreach ($this->attributes as $index => $attribute) {
            if ($attribute->key === $key) {
                unset($this->attributes[$index]);
            }
        }
    }
}
