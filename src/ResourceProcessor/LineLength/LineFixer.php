<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

interface LineFixer
{
    /**
     * @param array<string> $lines
     * @return array<string>
     */
    public function fix(array $lines, int $maximumLineLength): array;
}
