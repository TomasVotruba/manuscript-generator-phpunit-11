<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

final readonly class DelegatingLineFixer implements LineFixer
{
    /**
     * @param array<LineFixer> $lineFixers
     */
    public function __construct(
        private array $lineFixers
    ) {
    }

    public function fix(array $lines, int $maximumLineLength): array
    {
        $fixedLines = $lines;

        foreach ($this->lineFixers as $lineFixer) {
            $fixedLines = $lineFixer->fix($fixedLines, $maximumLineLength);
        }

        return $fixedLines;
    }
}
