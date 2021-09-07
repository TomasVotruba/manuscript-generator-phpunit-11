<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class Attribute extends AbstractNode
{
    public function __construct(
        public string $key,
        public string | bool $value
    ) {
    }
}
