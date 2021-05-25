<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

interface Node
{
    /**
     * @return array<string> The names of the public properties of this node that contain a subnode or an array of subnodes
     */
    public function subnodeNames(): array;

    public function setAttribute(string $key, mixed $value): void;

    public function getAttribute(string $key): mixed;
}
