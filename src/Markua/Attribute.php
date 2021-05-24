<?php

declare(strict_types=1);

namespace BookTools\Markua;

final class Attribute implements Node
{
    public function __construct(
        public string $key,
        public string $value
    ) {
    }

    public static function quote(string $value): string
    {
        return '"' . addslashes($value) . '"';
    }
}
