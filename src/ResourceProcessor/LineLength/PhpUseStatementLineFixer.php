<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor\LineLength;

final class PhpUseStatementLineFixer implements LineFixer
{
    public function fix(array $lines, int $maximumLineLength): array
    {
        $result = [];

        foreach ($lines as $line) {
            if (strlen($line) > $maximumLineLength && str_starts_with($line, 'use ')) {
                $isMatch = preg_match('/use (?<namespace>.+);/', $line, $matches);
                if ($isMatch === 1) {
                    $namespaceParts = explode('\\', $matches['namespace']);
                    if (count($namespaceParts) > 2) {
                        $abbreviated = [$namespaceParts[0], '...', $namespaceParts[count($namespaceParts) - 1]];
                        $line = 'use ' . implode('\\', $abbreviated) . '; // (abbreviated)';
                    }
                }
            }

            $result[] = $line;
        }

        return $result;
    }
}
