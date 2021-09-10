<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

final class Comment extends AbstractNode
{
    public function __construct(
        public string $text
    ) {
    }
}
