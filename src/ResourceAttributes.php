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
    ) {
    }

    public function setAttribute(Attribute $attribute): void
    {
        foreach ($this->attributes as $index => $existingAttribute) {
            if ($existingAttribute->hasKey($attribute->key())) {
                $this->attributes[$index] = $attribute;
                return;
            }
        }

        $this->attributes[] = $attribute;
    }

    public static function fromString(string $string): self
    {
        $attributes = new self([]);

        if (! str_starts_with($string, '{')) {
            return $attributes;
        }

        $string = trim($string, '{}');
        $keyValuePairs = explode(',', $string);
        foreach ($keyValuePairs as $keyValuePair) {
            list($key, $value) = explode(':', $keyValuePair);
            $key = trim($key);
            $value = trim($value);
            $attributes->setAttribute(new Attribute($key, $value));
        }

        return $attributes;
    }

    public function removeAttribute(string $key): void
    {
        foreach ($this->attributes as $index => $attribute) {
            if ($attribute->hasKey($key)) {
                unset($this->attributes[$index]);
            }
        }
    }

    public function asString(): string
    {
        $attributes = array_map(fn (Attribute $attribute) => $attribute->asString(), $this->attributes);

        return '{' . implode(', ', $attributes) . '}';
    }

    public function attribute(string $key): ?string
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->hasKey($key)) {
                return $attribute->value();
            }
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return $this->attributes === [];
    }
}
