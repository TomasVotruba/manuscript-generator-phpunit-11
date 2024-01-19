<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class Heading extends AbstractNode
{
    public function __construct(
        public int $level,
        public string $title,
        public AttributeList $attributes = new AttributeList()
    ) {
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
