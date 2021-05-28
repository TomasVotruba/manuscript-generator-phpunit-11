<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

final class Link extends AbstractNode
{
    public AttributeList $attributes;

    public function __construct(
        public string $target,
        public string $linkText,
        ?AttributeList $attributes = null
    ) {
        $this->attributes = $attributes ?? new AttributeList();
    }
}
