<?php

declare(strict_types=1);

namespace BookTools\Markua;

final class Attributes implements Node
{
    /**
     * @param array<Attribute> $attributes
     */
    public function __construct(
        private array $attributes
    ) {
    }
}
