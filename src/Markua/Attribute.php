<?php

declare(strict_types=1);

namespace BookTools\Markua;

final class Attribute implements Node
{
    public function __construct(
        private string $key,
        private string $value
    ) {
    }
}
