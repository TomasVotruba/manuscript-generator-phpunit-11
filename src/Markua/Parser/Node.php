<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

interface Node
{
    /**
     * @return array<Node>
     */
    public function subnodes(): array;
}
