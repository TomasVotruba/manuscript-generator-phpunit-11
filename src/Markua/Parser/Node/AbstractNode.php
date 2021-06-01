<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use ManuscriptGenerator\Markua\Parser\Node;

abstract class AbstractNode implements Node
{
    /**
     * @var array<string,mixed>
     */
    private array $internalAttributes = [];

    public function setAttribute(string $key, mixed $value): void
    {
        $this->internalAttributes[$key] = $value;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->internalAttributes[$key] ?? null;
    }

    public function subnodeNames(): array
    {
        return [];
    }
}
