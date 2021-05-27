<?php

declare(strict_types=1);

namespace BookTools\ResourceProcessor;

final class InsignificantWhitespaceStripper
{
    public function strip(string $input): string
    {
        $output = $input;

        $output = preg_replace("/([\s]+)\n$/", "$2\n", $output);
        assert(is_string($output));

        // strip trailing newlines but keep one
        return rtrim($output, "\n") . "\n";
    }
}