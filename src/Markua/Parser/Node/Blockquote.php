<?php

declare(strict_types=1);

namespace ManuscriptGenerator\Markua\Parser\Node;

use ManuscriptGenerator\Markua\Parser\Node;

final class Blockquote extends AbstractNode
{
    /**
     * @param array<Node> $subnodes
     */
    public function __construct(
        public array $subnodes
    ) {
    }

    public function subnodeNames(): array
    {
        return ['subnodes'];
    }
}
