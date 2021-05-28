<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser\Node;

final class Attribute extends AbstractNode
{
    public function __construct(
        public string $key,
        public string $value
    ) {
    }

    /**
     * @deprecated Rely on Markdown printer to do this
     */
    public static function quote(string $value): string
    {
        return '"' . addslashes($value) . '"';
    }
}
