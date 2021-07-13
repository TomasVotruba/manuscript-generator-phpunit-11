<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

final class InsignificantWhitespaceStripper
{
    public function strip(string $input): string
    {
        $output = $input;

        // First remove trailing spaces and tabs from each line
        $output = preg_replace("/([ \t]+)\n/", "$2\n", $output);
        assert(is_string($output));

        // Then remove empty lines from the beginning of the string because they can be considered insignificant as well
        $output = ltrim($output, "\n");

        // Strip trailing newlines but keep one
        return rtrim($output, "\n") . "\n";
    }
}
