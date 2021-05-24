<?php

declare(strict_types=1);

namespace BookTools\Markua\Parser;

final class Heading implements Node
{
    public function __construct(
        private int $level,
        private string $title
    ) {
    }
}
