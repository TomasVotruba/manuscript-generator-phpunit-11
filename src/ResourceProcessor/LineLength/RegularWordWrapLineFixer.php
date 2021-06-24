<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

final class RegularWordWrapLineFixer implements LineFixer
{
    public function fix(array $lines, int $maximumLineLength): array
    {
        $result = [];

        foreach ($lines as $line) {
            if (strlen($line) > $maximumLineLength) {
                foreach (explode("\n", wordwrap($line, $maximumLineLength, "\n", false)) as $fixedLine) {
                    $result[] = $fixedLine;
                }
            } else {
                $result[] = $line;
            }
        }

        return $result;
    }
}
