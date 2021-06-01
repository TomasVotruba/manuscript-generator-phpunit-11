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

    public function set(string $key, string $value): void
    {
        foreach ($this->attributes as $index => $existingAttribute) {
            if ($existingAttribute->key === $key) {
                $this->attributes[$index]->value = $value;
                return;
            }
        }

        $this->attributes[] = new Attribute($key, $value);
    }

    public function get(string $key): ?string
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->key === $key) {
                return $attribute->value;
            }
        }

        return null;
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
}
