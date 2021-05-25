<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

use BookTools\Markua\Parser\Node;

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
}
