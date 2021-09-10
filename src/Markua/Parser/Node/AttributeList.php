<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class AttributeList extends AbstractNode
{
    /**
     * @param array<Attribute> $attributes
     */
    public function __construct(
        public array $attributes = []
    ) {
    }

    public function set(string $key, string|bool|int $value): void
    {
        foreach ($this->attributes as $index => $existingAttribute) {
            if ($existingAttribute->key === $key) {
                $this->attributes[$index]->value = $value;
                return;
            }
        }

        $this->attributes[] = new Attribute($key, $value);
    }

    public function get(string $key): string|bool|int|null
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->key === $key) {
                return $attribute->value;
            }
        }

        return null;
    }

    public function getStringOrNull(string $key): ?string
    {
        $value = $this->get($key);

        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidAttributeType($key, 'string', $value);
        }

        return $value;
    }

    public function getBoolOrNull(string $key): ?bool
    {
        $value = $this->get($key);

        if ($value === null) {
            return null;
        }

        if (! is_bool($value)) {
            throw new InvalidAttributeType($key, 'bool', $value);
        }

        return $value;
    }

    public function getIntOrNull(string $key): ?int
    {
        $value = $this->get($key);

        if ($value === null) {
            return null;
        }

        if (! is_int($value)) {
            throw new InvalidAttributeType($key, 'int', $value);
        }

        return $value;
    }

    public function remove(string $key): void
    {
        foreach ($this->attributes as $index => $attribute) {
            if ($attribute->key === $key) {
                unset($this->attributes[$index]);
            }
        }
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }

    public function has(string $key): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->key === $key) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return count($this->attributes) === 0;
    }
}
