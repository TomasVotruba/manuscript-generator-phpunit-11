<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Paragraph implements Node
{
    public function __construct(
        public string $text
    ) {
    }
}
