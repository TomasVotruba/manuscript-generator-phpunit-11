<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class Heading extends AbstractNode
{
    public AttributeList $attributes;

    public function __construct(
        public int $level,
        public string $title,
        ?AttributeList $attributes = null
    ) {
        $this->attributes = $attributes === null ? new AttributeList() : $attributes;
    }

    public function subnodeNames(): array
    {
        return ['attributes'];
    }
}
