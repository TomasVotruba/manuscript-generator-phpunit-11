<?php

declare(strict_types=1);

namespace ManuscriptGenerator\ResourceProcessor;

use RuntimeException;

final readonly class TextSkipper
{
    private const DEFAULT_SKIP_START_MARKER = '// skip-start';

    private const DEFAULT_SKIP_END_MARKER = '// skip-end';

    private const DEFAULT_REPLACEMENT = '// ...';

    private string $startMarker;

    private string $endMarker;

    private string $replacement;

    public function __construct(
        ?string $startMarker = null,
        ?string $endMarker = null,
        ?string $replacement = null
    ) {
        $this->startMarker = $startMarker ?: self::DEFAULT_SKIP_START_MARKER;
        $this->endMarker = $endMarker ?: self::DEFAULT_SKIP_END_MARKER;
        $this->replacement = $replacement ?: self::DEFAULT_REPLACEMENT;
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

        if ($startPosition > $endPosition) {
            throw new RuntimeException(sprintf(
                'End marker (%s) found before start marker (%s)',
                $this->endMarker,
                $this->startMarker
            ));
        }

        $skipped = substr($text, 0, $startPosition) . $this->replacement . substr(
            $text,
            $endPosition + strlen($this->endMarker)
        );

        // Run again until there are no more parts to skip
        return $this->skipParts($skipped);
    }
}
