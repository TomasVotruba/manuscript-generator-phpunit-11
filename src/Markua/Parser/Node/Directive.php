<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

final class Directive extends AbstractNode
{
    public function __construct(
        public string $name
    ) {
    }
}
