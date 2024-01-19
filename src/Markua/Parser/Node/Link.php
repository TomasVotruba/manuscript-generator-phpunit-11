<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class Link extends AbstractNode
{
    public function __construct(
        public string $target,
        public string $linkText,
        public AttributeList $attributes = new AttributeList()
    ) {
    }
}
