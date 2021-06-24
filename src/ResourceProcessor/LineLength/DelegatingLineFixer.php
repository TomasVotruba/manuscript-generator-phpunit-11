<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

final class DelegatingLineFixer implements LineFixer
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

        foreach ($fixedLines as $line) {
            if (strlen($line) > $maximumLineLength) {
                throw new CouldNotFixLine('None of the line fixers was able to fix this line: ' . $line);
            }
        }

        return $fixedLines;
    }
}
