<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use RuntimeException;

final class TextSkipper
{
    public function __construct(
        private string $startMarker,
        private string $endMarker,
        private string $replacement
    ) {
    }

    public function skipParts(string $text): string
    {
        $startPosition = strpos($text, $this->startMarker);
        $endPosition = strpos($text, $this->endMarker);

        if ($startPosition === false && $endPosition === false) {
            // No need to skip anything
            return $text;
        }

        if ($startPosition === false) {
            throw new RuntimeException(sprintf('Start marker not found (%s)', $this->startMarker));
        }
        if ($endPosition === false) {
            throw new RuntimeException(sprintf('End marker not found (%s)', $this->endMarker));
        }

        $skipped = substr($text, 0, $startPosition) . $this->replacement . substr(
            $text,
            $endPosition + strlen($this->endMarker)
        );

        // Run again until there are no more parts to skip
        return $this->skipParts($skipped);
    }
}
